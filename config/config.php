<?php
// Configuración general del sistema

// URL base del proyecto - Método más robusto
$script_path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$base_url = str_replace('/index.php', '', $script_path);
$base_url = str_replace('/libros', '', $base_url);
$base_url = str_replace('/usuarios', '', $base_url);
$base_url = str_replace('/prestamos', '', $base_url);
$base_url = rtrim($base_url, '/');

// Si la detección falla, usar ruta manual como fallback
if (empty($base_url)) {
    $base_url = '';
}

define('BASE_URL', $base_url);

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (cambiar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>