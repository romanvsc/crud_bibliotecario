<?php
// Incluir configuración
require_once __DIR__ . '/config/config.php';

$titulo_pagina = 'Dashboard';
include 'includes/header.php';

require 'token_confirmar.php';
require 'obtenerBaseDeDatos.php';
redirigir(comprobarToken(ObtenerDB()));
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Panel de Control</h1>
        <p class="subtitle">Bienvenido al Sistema de Gestión de Biblioteca</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h3 id="total-libros">0</h3>
                <p>Total Libros</p>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3 id="total-usuarios">0</h3>
                <p>Usuarios Registrados</p>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-hand-holding"></i>
            </div>
            <div class="stat-content">
                <h3 id="prestamos-activos">0</h3>
                <p>Préstamos Activos</p>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3 id="prestamos-vencidos">0</h3>
                <p>Préstamos Vencidos</p>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Actividad Reciente</h2>
            </div>
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon activity-success">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong>Nuevo préstamo registrado</strong></p>
                        <small>Hace 5 minutos</small>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon activity-primary">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong>Nuevo libro agregado</strong></p>
                        <small>Hace 1 hora</small>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon activity-warning">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div class="activity-content">
                        <p><strong>Libro devuelto</strong></p>
                        <small>Hace 2 horas</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-link"></i> Accesos Rápidos</h2>
            </div>
            <div class="quick-actions">
                <a href="<?php echo BASE_URL; ?>/prestamos/nuevo.php" class="action-card action-primary">
                    <i class="fas fa-plus-circle"></i>
                    <span>Nuevo Préstamo</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/libros/crear.php" class="action-card action-success">
                    <i class="fas fa-book-medical"></i>
                    <span>Agregar Libro</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/usuarios/crear.php" class="action-card action-info">
                    <i class="fas fa-user-plus"></i>
                    <span>Nuevo Usuario</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/prestamos/historial.php" class="action-card action-warning">
                    <i class="fas fa-history"></i>
                    <span>Ver Historial</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>