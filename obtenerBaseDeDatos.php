<?php

if (!function_exists('ObtenerDB')) {
    function ObtenerDB(){
        $servidor = "localhost";
        $usuario = "root";
        $clave = "";
        $DB = "biblioteca";
        $con = new mysqli($servidor, $usuario, $clave, $DB);
        if ($con->connect_error) {
            die("Error de conexión con la base de datos: " . $con -> connect_error);
        }
        return $con;
    }
}

?>