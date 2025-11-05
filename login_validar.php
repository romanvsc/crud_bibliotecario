<?php
// Reanuda la Sesion PHP.
session_start();

// indicamos que la salida será JSON
header("Content-Type: application/json; charset=UTF-8");

$con = ObtenerDB();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombreDeUsuario = $_POST["usuario"] ?? "";
    $contraseñaDeUsuario = $_POST["contraseña"] ?? "";

    $sesion = ComprobarUsuario($con, $nombreDeUsuario, $contraseñaDeUsuario);
    if ($sesion === true){

    } else {

    }

}

$con->close();

// ------- Funciones ---------

function ObtenerDB(){
    $servidor = "localhost";
    $usuario = "root";
    $clave = "";
    $DB = "biblioteca";
    $con = new mysqli($servidor, $usuario, $clave, $DB);
    if ($conn->connect_error) {
        die(json_encode(["success" => false, "error" => "Error de conexión BD"]));
    }
    return $con;
}

function ComprobarUsuario($con, $usuario, $contraseña){
    $stmt = $con->prepare("SELECT 'password' FROM usuarios_sistema WHERE usuario = ? LIMIT 1");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($contraseña_hasheada);
        $stmt->fetch();

        if(password_verify($contraseña, $contraseña_hasheada)){
            $retorno = true;
        } else {
            $retorno = false;
        }
    } else {
        $nombre = False;
    }

    $stmt->close();

    return $nombre;
}

?>