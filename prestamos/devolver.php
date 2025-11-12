<?php
/**
 * Devolver un libro prestado
 * Marca el préstamo como devuelto y actualiza el estado del libro
 */

// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden devolver libros
verificarRol(['admin', 'bibliotecario']);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

// Obtener ID del préstamo
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$con = ObtenerDB();

// Obtener información del préstamo
$query = $con->prepare("SELECT p.*, 
                        l.titulo as libro_titulo,
                        l.autor as libro_autor,
                        u.nombre_completo as usuario_nombre,
                        DATEDIFF(CURDATE(), p.fecha_devolucion) as dias_retraso
                        FROM prestamos p
                        INNER JOIN libros l ON p.libro_id = l.id
                        INNER JOIN usuarios u ON p.usuario_id = u.id
                        WHERE p.id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$prestamo = $result->fetch_assoc();

if (!$prestamo) {
    $query->close();
    $con->close();
    header('Location: index.php');
    exit;
}

$query->close();

// Verificar que el préstamo esté activo
if ($prestamo['estado'] !== 'activo') {
    $_SESSION['error_devolver'] = 'El préstamo ya fue devuelto anteriormente.';
    $con->close();
    header('Location: index.php');
    exit;
}

// Confirmar devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Iniciar transacción
    $con->begin_transaction();
    
    try {
        // Actualizar el préstamo
        $update_prestamo = $con->prepare("UPDATE prestamos SET 
                                          estado = 'devuelto', 
                                          fecha_dev_real = CURDATE()
                                          WHERE id = ?");
        $update_prestamo->bind_param("i", $id);
        $update_prestamo->execute();
        
        // Actualizar el estado del libro a 'disponible'
        $update_libro = $con->prepare("UPDATE libros SET estado = 'disponible' WHERE id = ?");
        $update_libro->bind_param("i", $prestamo['libro_id']);
        $update_libro->execute();
        
        $con->commit();
        
        $_SESSION['success_devolver'] = 'El libro "' . $prestamo['libro_titulo'] . '" ha sido devuelto exitosamente.';
        
        $update_prestamo->close();
        $update_libro->close();
    } catch (Exception $e) {
        $con->rollback();
        $_SESSION['error_devolver'] = 'Error al devolver el libro: ' . $e->getMessage();
    }
    
    $con->close();
    header('Location: index.php');
    exit;
}

$titulo_pagina = 'Devolver Libro';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-undo-alt"></i> Confirmar Devolución</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    <?php if ($prestamo['dias_retraso'] > 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Atención:</strong> Este préstamo tiene <?php echo $prestamo['dias_retraso']; ?> día(s) de retraso.
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <div>
                Devolución en tiempo.
            </div>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <div class="confirm-box">
            <div class="confirm-icon confirm-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>¿Confirmar devolución del libro?</h2>
            
            <div class="prestamo-info">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-book"></i> Libro:</span>
                    <span class="info-value">
                        <strong><?php echo htmlspecialchars($prestamo['libro_titulo']); ?></strong>
                        <br><small><?php echo htmlspecialchars($prestamo['libro_autor']); ?></small>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user"></i> Usuario:</span>
                    <span class="info-value"><?php echo htmlspecialchars($prestamo['usuario_nombre']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar"></i> Fecha de Préstamo:</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar-check"></i> Fecha de Devolución:</span>
                    <span class="info-value">
                        <?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion'])); ?>
                        <?php if ($prestamo['dias_retraso'] > 0): ?>
                            <br><small class="text-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?php echo $prestamo['dias_retraso']; ?> días de retraso
                            </small>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock"></i> Fecha de Devolución Real:</span>
                    <span class="info-value"><strong><?php echo date('d/m/Y'); ?></strong></span>
                </div>
                <?php if (!empty($prestamo['observaciones'])): ?>
                    <div class="info-row">
                        <span class="info-label"><i class="fas fa-comment"></i> Observaciones:</span>
                        <span class="info-value"><?php echo htmlspecialchars($prestamo['observaciones']); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <p class="confirm-text">
                Al confirmar, el libro volverá a estar disponible para préstamos.
            </p>
            
            <div class="form-actions">
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirmar Devolución
                    </button>
                </form>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$con->close();
include '../includes/footer.php';
?>
