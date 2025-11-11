<?php
/**
 * Cierra la sesión del usuario
 * Elimina el token de la base de datos y la cookie del navegador
 */

session_start();

require_once __DIR__ . '/obtenerBaseDeDatos.php';
require_once __DIR__ . '/config/config.php';

// Obtener el token de la cookie
$token = $_COOKIE['sesion_usuario'] ?? null;

if ($token) {
    // Conectar a la base de datos
    $con = ObtenerDB();
    
    // Eliminar el token de la base de datos
    $query = $con->prepare("DELETE FROM expiracion_cookie WHERE token = ?");
    $query->bind_param("s", $token);
    $query->execute();
    $query->close();
    
    $con->close();
    
    // Eliminar la cookie del navegador
    setcookie("sesion_usuario", "", time() - 3600, "/");
}

// Destruir la sesión de PHP
session_unset();
session_destroy();

// Redirigir al login
header("Location: " . BASE_URL . "/login.php");
exit;
?>
