<?php
/**
 * Historial completo de préstamos
 * Muestra todos los préstamos con estadísticas
 */

// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden ver el historial completo
verificarRol(['admin', 'bibliotecario']);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$titulo_pagina = 'Historial de Préstamos';

// Obtener todos los préstamos
$con = ObtenerDB();

// Obtener estadísticas
$stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = 'devuelto' THEN 1 ELSE 0 END) as devueltos,
                SUM(CASE WHEN estado = 'activo' AND fecha_devolucion < CURDATE() THEN 1 ELSE 0 END) as vencidos
                FROM prestamos";
$stats_result = $con->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Obtener todos los préstamos
$query = "SELECT p.*, 
          l.titulo as libro_titulo, 
          l.autor as libro_autor,
          u.nombre_completo as usuario_nombre,
          u.dni as usuario_dni,
          DATEDIFF(CURDATE(), p.fecha_devolucion) as dias_retraso
          FROM prestamos p
          INNER JOIN libros l ON p.libro_id = l.id
          INNER JOIN usuarios u ON p.usuario_id = u.id
          ORDER BY p.fecha_prestamo DESC";

$result = $con->query($query);
$prestamos = $result->fetch_all(MYSQLI_ASSOC);

$con->close();

include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-history"></i> Historial de Préstamos</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card stat-primary">
            <div class="stat-icon">
                <i class="fas fa-list"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Préstamos</p>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['activos']; ?></h3>
                <p>Activos</p>
            </div>
        </div>

        <div class="stat-card stat-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['devueltos']; ?></h3>
                <p>Devueltos</p>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $stats['vencidos']; ?></h3>
                <p>Vencidos</p>
            </div>
        </div>
    </div>

    <!-- Tabla de préstamos -->
    <?php if (count($prestamos) > 0): ?>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Libro</th>
                    <th>Fecha Préstamo</th>
                    <th>Fecha Devolución</th>
                    <th>Devolución Real</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prestamos as $prestamo): ?>
                    <?php
                    $es_vencido = $prestamo['estado'] === 'vencido' && $prestamo['dias_retraso'] > 0;
                    $row_class = $es_vencido ? 'row-warning' : '';
                    ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td>#<?php echo str_pad($prestamo['id'], 3, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <div class="user-cell">
                                <i class="fas fa-user-circle"></i>
                                <div>
                                    <strong><?php echo htmlspecialchars($prestamo['usuario_nombre']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($prestamo['usuario_dni']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="book-cell">
                                <i class="fas fa-book"></i>
                                <div>
                                    <strong><?php echo htmlspecialchars($prestamo['libro_titulo']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($prestamo['libro_autor']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion'])); ?>
                            <?php if ($es_vencido): ?>
                                <br><small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <?php echo abs($prestamo['dias_retraso']); ?> días de retraso
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($prestamo['fecha_dev_real'])): ?>
                                <?php echo date('d/m/Y', strtotime($prestamo['fecha_dev_real'])); ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($prestamo['estado'] === 'devuelto'): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Devuelto
                                </span>
                            <?php elseif ($es_vencido): ?>
                                <span class="badge badge-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Vencido
                                </span>
                            <?php else: ?>
                                <span class="badge badge-primary">
                                    <i class="fas fa-clock"></i> Activo
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <p>No hay préstamos registrados</p>
        <a href="nuevo.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Registrar primer préstamo
        </a>
    </div>
    <?php endif; ?>

    <div class="info-footer">
        <p><i class="fas fa-info-circle"></i> Total de registros: <strong><?php echo count($prestamos); ?></strong></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
