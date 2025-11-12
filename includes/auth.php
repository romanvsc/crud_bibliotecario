<?php
/**
 * Middleware de autenticación
 * Verifica que el usuario tenga una sesión válida antes de acceder a las páginas protegidas
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir funciones necesarias
require_once __DIR__ . '/../obtenerBaseDeDatos.php';
require_once __DIR__ . '/../token_confirmar.php';

// Obtener conexión a la base de datos
$con = ObtenerDB();

// Verificar el token y redirigir si es necesario
$debe_redirigir = comprobarToken($con);
redirigir($debe_redirigir);

// Obtener información del usuario autenticado
$usuario_logueado = null;
$token = $_COOKIE['sesion_usuario'] ?? null;

if ($token) {
    $query = $con->prepare("SELECT us.id, us.usuario, us.nombre, us.email, us.rol 
                            FROM expiracion_cookie ec 
                            INNER JOIN usuarios_sistema us ON ec.usuario_id = us.id 
                            WHERE ec.token = ? AND ec.expiracion > NOW()");
    $query->bind_param("s", $token);
    $query->execute();
    $resultado = $query->get_result();
    
    if ($resultado->num_rows > 0) {
        $usuario_logueado = $resultado->fetch_assoc();
    }
    
    $query->close();
}

// Cerrar conexión
$con->close();

// Si no hay usuario logueado, redirigir a login
if (!$usuario_logueado) {
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/login.php");
    exit;
}
?>
