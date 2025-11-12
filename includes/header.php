<?php
// Incluir configuración si no está incluida
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// Determinar la ruta relativa para los assets
$depth = substr_count($_SERVER['SCRIPT_NAME'], '/') - substr_count(BASE_URL, '/') - 1;
$relative_path = str_repeat('../', $depth);

require __DIR__ . '/../token_confirmar.php';
require __DIR__ . '/../obtenerBaseDeDatos.php';
redirigir(comprobarToken(ObtenerDB()));

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?>Sistema Biblioteca</title>
    <link rel="stylesheet" href="<?php echo $relative_path; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo $relative_path; ?>assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <i class="fas fa-book"></i>
                <span>Sistema Biblioteca</span>
            </div>
            <ul class="navbar-menu">
                <?php if (isset($usuario_logueado['rol']) && $usuario_logueado['rol'] === 'usuario'): ?>
                    <!-- Menú para usuarios comunes -->
                    <li><a href="<?php echo BASE_URL; ?>/prestamos/mis_prestamos.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/prestamos/mis_prestamos.php') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Mis Préstamos
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>/libros/ver.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/libros/ver.php') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-book-open"></i> Ver Libros
                    </a></li>
                <?php else: ?>
                    <!-- Menú para admin y bibliotecarios -->
                    <li><a href="<?php echo BASE_URL; ?>/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>/libros/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/libros/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-book-open"></i> Libros
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>/usuarios/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/usuarios/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-users"></i> Usuarios
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>/prestamos/index.php" class="<?php echo strpos($_SERVER['PHP_SELF'], '/prestamos/') !== false ? 'active' : ''; ?>">
                        <i class="fas fa-exchange-alt"></i> Préstamos
                    </a></li>
                <?php endif; ?>
            </ul>
            <div class="navbar-user">
                <i class="fas fa-user-circle"></i>
                <span><?php echo isset($usuario_logueado) ? htmlspecialchars($usuario_logueado['nombre']) : 'Usuario'; ?></span>
                <div class="user-dropdown">
                    <?php if (isset($usuario_logueado)): ?>
                        <div class="user-info">
                            <p><strong><?php echo htmlspecialchars($usuario_logueado['nombre']); ?></strong></p>
                            <p class="user-role"><?php echo ucfirst($usuario_logueado['rol']); ?></p>
                        </div>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <main class="main-content">