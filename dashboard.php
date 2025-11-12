<?php
/**
 * Dashboard principal del sistema
 * Muestra estadísticas y accesos rápidos
 */

// Incluir autenticación
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/check_role.php';

// Redirigir a usuarios comunes a su página de préstamos
if (esUsuarioComun()) {
    header("Location: prestamos/mis_prestamos.php");
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/obtenerBaseDeDatos.php';

$titulo_pagina = 'Panel de Control';

// Obtener estadísticas de la base de datos
$con = ObtenerDB();

// Total de libros
$result = $con->query("SELECT COUNT(*) as total FROM libros");
$total_libros = $result->fetch_assoc()['total'];

// Total de usuarios
$result = $con->query("SELECT COUNT(*) as total FROM usuarios");
$total_usuarios = $result->fetch_assoc()['total'];

// Préstamos activos
$result = $con->query("SELECT COUNT(*) as total FROM prestamos WHERE estado = 'activo'");
$prestamos_activos = $result->fetch_assoc()['total'];

// Préstamos vencidos
$result = $con->query("SELECT COUNT(*) as total FROM prestamos 
                      WHERE estado = 'activo' AND fecha_devolucion < CURDATE()");
$prestamos_vencidos = $result->fetch_assoc()['total'];

// Libros disponibles
$result = $con->query("SELECT COUNT(*) as total FROM libros WHERE estado = 'disponible'");
$libros_disponibles = $result->fetch_assoc()['total'];

// Actividad reciente - últimos préstamos
$prestamos_recientes = $con->query("SELECT p.*, l.titulo as libro_titulo, u.nombre_completo as usuario_nombre
                                    FROM prestamos p
                                    INNER JOIN libros l ON p.libro_id = l.id
                                    INNER JOIN usuarios u ON p.usuario_id = u.id
                                    ORDER BY p.created_at DESC
                                    LIMIT 5");

$con->close();

include 'includes/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-chart-line"></i> Panel de Control</h1>
            <p class="subtitle">Bienvenido, <?php echo htmlspecialchars($usuario_logueado['nombre']); ?></p>
        </div>
        <div class="header-actions">
            <span class="badge badge-info">
                <i class="fas fa-user-shield"></i> <?php echo ucfirst($usuario_logueado['rol']); ?>
            </span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_libros; ?></h3>
                <p>Total Libros</p>
                <small class="stat-detail">
                    <i class="fas fa-check-circle"></i> <?php echo $libros_disponibles; ?> disponibles
                </small>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_usuarios; ?></h3>
                <p>Usuarios Registrados</p>
                <small class="stat-detail">
                    <i class="fas fa-user-check"></i> Activos
                </small>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">
                <i class="fas fa-hand-holding"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $prestamos_activos; ?></h3>
                <p>Préstamos Activos</p>
                <small class="stat-detail">
                    <i class="fas fa-clock"></i> En curso
                </small>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $prestamos_vencidos; ?></h3>
                <p>Préstamos Vencidos</p>
                <small class="stat-detail">
                    <i class="fas fa-calendar-times"></i> Requieren atención
                </small>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <div class="section-header">
                <h2><i class="fas fa-clock"></i> Actividad Reciente</h2>
                <a href="<?php echo BASE_URL; ?>/prestamos/index.php" class="btn-link">Ver todo</a>
            </div>
            <div class="activity-list">
                <?php if ($prestamos_recientes && $prestamos_recientes->num_rows > 0): ?>
                    <?php while ($prestamo = $prestamos_recientes->fetch_assoc()): ?>
                        <?php
                        $tiempo = time() - strtotime($prestamo['created_at']);
                        if ($tiempo < 60) {
                            $tiempo_texto = "Hace " . $tiempo . " segundos";
                        } elseif ($tiempo < 3600) {
                            $tiempo_texto = "Hace " . floor($tiempo / 60) . " minutos";
                        } elseif ($tiempo < 86400) {
                            $tiempo_texto = "Hace " . floor($tiempo / 3600) . " horas";
                        } else {
                            $tiempo_texto = "Hace " . floor($tiempo / 86400) . " días";
                        }
                        
                        $icon_class = 'activity-primary';
                        $icon = 'fa-book';
                        if ($prestamo['estado'] === 'devuelto') {
                            $icon_class = 'activity-success';
                            $icon = 'fa-check-circle';
                        } elseif ($prestamo['estado'] === 'activo') {
                            $icon_class = 'activity-warning';
                            $icon = 'fa-hand-holding';
                        }
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $icon_class; ?>">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <p><strong><?php echo htmlspecialchars($prestamo['usuario_nombre']); ?></strong> 
                                   - <?php echo htmlspecialchars($prestamo['libro_titulo']); ?></p>
                                <small><?php echo $tiempo_texto; ?> - Estado: <?php echo ucfirst($prestamo['estado']); ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay actividad reciente</p>
                    </div>
                <?php endif; ?>
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

    <?php if ($prestamos_vencidos > 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Atención:</strong> Hay <?php echo $prestamos_vencidos; ?> préstamo(s) vencido(s) que requieren atención.
            <a href="<?php echo BASE_URL; ?>/prestamos/index.php?estado=vencido" class="alert-link">Ver préstamos vencidos</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
