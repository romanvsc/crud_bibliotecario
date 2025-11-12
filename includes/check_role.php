<?php
/**
 * Middleware de verificación de roles
 * Verifica que el usuario tenga los permisos adecuados
 */

// Este archivo debe incluirse después de auth.php

/**
 * Verifica si el usuario tiene uno de los roles permitidos
 * @param array $roles_permitidos Array de roles que pueden acceder
 */
function verificarRol($roles_permitidos = ['admin', 'bibliotecario']) {
    global $usuario_logueado;
    
    if (!isset($usuario_logueado['rol']) || !in_array($usuario_logueado['rol'], $roles_permitidos)) {
        // Si el usuario no tiene el rol adecuado, redirigir según su rol
        if ($usuario_logueado['rol'] === 'usuario') {
            // Usuarios comunes van a su panel de préstamos
            header("Location: /biblioteca/crud_bibliotecario/prestamos/mis_prestamos.php");
        } else {
            // Otros casos, ir al dashboard
            header("Location: /biblioteca/crud_bibliotecario/dashboard.php");
        }
        exit;
    }
}

/**
 * Verifica si el usuario es administrador
 */
function esAdmin() {
    global $usuario_logueado;
    return isset($usuario_logueado['rol']) && $usuario_logueado['rol'] === 'admin';
}

/**
 * Verifica si el usuario es bibliotecario o admin
 */
function esBibliotecarioOAdmin() {
    global $usuario_logueado;
    return isset($usuario_logueado['rol']) && in_array($usuario_logueado['rol'], ['admin', 'bibliotecario']);
}

/**
 * Verifica si el usuario es un usuario común
 */
function esUsuarioComun() {
    global $usuario_logueado;
    return isset($usuario_logueado['rol']) && $usuario_logueado['rol'] === 'usuario';
}
