<?php
// Reanuda la Sesion PHP.
session_start();

// indicamos que la salida será JSON
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/obtenerBaseDeDatos.php';

$con = ObtenerDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombreDeUsuario = $_POST["usuario"] ?? "";
    $contraseñaDeUsuario = $_POST["contraseña"] ?? "";

    $sesion = ComprobarUsuario($con, $nombreDeUsuario, $contraseñaDeUsuario);
    if ($sesion === true){
        $token = bin2hex(random_bytes(16)); 
        $tiempoDeVida = 3600;
        $expiracion_DB = date("Y-m-d H:i:s", time() + $tiempoDeVida);
        setcookie("sesion_usuario", $token, time() + $tiempoDeVida, "/", "", false, true);
        $usuario_id = ObtenerUsuarioId($con, $nombreDeUsuario);
        
        cargarCookie_ALaBaseDeDatos($con, $token, $usuario_id, $expiracion_DB);

    /*  < setcookie >
    .   "sesion_ | el nombre de la cookie.
    .   $token   | el valor que se guarda dentro de la cookie.
    .   time() + | define la expiración de la cookie. time() devuelve la hora actual, le sumamos los seg que durá la cookie.
    .   "/"      | significa que está cookie estará disponible en todo el dominio.
    .   ""       | es dónde debe ir escrito el dominio, pero al ponerlo vácio este se aplica automaticamente.
    .   false    | Define sí la cookie debe enviarse solamente por HTTPS o no (Ahora está en false, se permite HTTP).
    .   Httponly | Si está en 'true' la cookie no será accesible desde JavaScript (solo el servidor podrá leerla),
    .              lo cual quiere decir que solo por HTTP "peticiones web normales" y no por JavaScript Ej: document.cookie. 
    */ 

        echo json_encode([
            "success" => true
        ]);
    } else {
        echo json_encode([
            "success" => false
        ]);
    }

    $con->close();
    exit;

}



// ------- Funciones ---------

function ComprobarUsuario($con, $usuario, $contraseña){
    $stmt = $con->prepare("SELECT password FROM usuarios_sistema WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $contraseña_hasheada = "";
        $stmt->bind_result($contraseña_hasheada);
        $stmt->fetch();

        if(password_verify($contraseña, $contraseña_hasheada)){
            $retorno = true;
        } else {
            $retorno = false;
        }
    } else {
        $retorno = false;
    }

    $stmt->close();
    return $retorno;
}

function ObtenerUsuarioId($con, $usuario){

    $stmt = $con->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0) {
        $usuario_id = null;
        $stmt->bind_result($usuario_id);
        $stmt->fetch();
        $stmt->fetch();
    } else { 
        $usuario_id = null; 
    }

    $stmt->close();

    return $usuario_id;

}

function cargarCookie_ALaBaseDeDatos($con, $token, $usuario_id, $expiracion_DB) {
    // Primero verificamos si ya existe un token para este usuario
    $check = $con->prepare("SELECT token FROM expiracion_cookie WHERE usuario_id = ?");
    $check->bind_param("i", $usuario_id);
    $check->execute();
    $resultado = $check->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        // Si existe, actualizamos el token y la expiración
        $update = $con->prepare("UPDATE expiracion_cookie SET token = ?, expiracion = ? WHERE usuario_id = ?");
        $update->bind_param("ssi", $token, $expiracion_DB, $usuario_id);
        $update->execute();
        $update->close();
    } else {
        // Si no existe, insertamos uno nuevo
        $insert = $con->prepare("INSERT INTO expiracion_cookie (token, usuario_id, expiracion) VALUES (?, ?, ?)");
        $insert->bind_param("sis", $token, $usuario_id, $expiracion_DB);
        $insert->execute();
        $insert->close();
    }

    if ($resultado) {
        $resultado->close();
    }
    $check->close();
}
?>