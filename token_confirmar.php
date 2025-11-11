<?php
// La conexión no se cierra en está función.

function comprobarToken($conexion){
    $paginaActual = basename($_SERVER['PHP_SELF']);
    $token = $_COOKIE['sesion_usuario'] ?? null;
    $redirigir = false;

    // El $token existe, entonces...
    if ($token) {
        $query = $conexion -> prepare("SELECT expiracion FROM expiracion_cookie WHERE token = ?");
        $query -> bind_param("s", $token);
        $query -> execute();
        $resultado = $query -> get_result();
        $fila = $resultado -> fetch_assoc();
        $resultado -> free();

        // Si no existe o expiró...
        if (empty($fila['expiracion']) || strtotime($fila['expiracion']) < time()) {

            // borra la cookie, y luego la sesion.
            setcookie("sesion_usuario", "", time() - 3600, "/");
            $del = $conexion -> prepare("DELETE FROM expiracion_cookie WHERE token = ?");
            $del -> bind_param("s", $token);
            $del -> execute();

            // Si no esta en 'login.php'...
            if ($paginaActual !== 'login.php') { $redirigir = true; }

        }

        // cerrar el $query y $del si existe.
        if (isset($query)) { $query -> close(); }
        if (isset($del)) { $del -> close(); }
        
        
    } else { $redirigir = true; }
    // ▲ El token no existe, entonces...
    
    return $redirigir;

}

function redirigir($redirigir){
    $paginaActual = basename($_SERVER['PHP_SELF']);

    if($paginaActual == 'login.php' && $redirigir === false) {
        header("Location: index.php");
        exit;
    }

    // Si no esta en 'login.php' -> redirigir con header.
    if ($paginaActual !== 'login.php' && $redirigir === true) {
        header("Location: login.php");
        exit;
    }
}

?>