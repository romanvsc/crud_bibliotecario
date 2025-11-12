<?php
// Incluir autenticación
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/check_role.php';

// Redirigir usuarios comunes a su página de préstamos
if (esUsuarioComun()) {
    header("Location: prestamos/mis_prestamos.php");
    exit;
}

// Para admin y bibliotecarios, redirigir al dashboard
header("Location: dashboard.php");
exit;
