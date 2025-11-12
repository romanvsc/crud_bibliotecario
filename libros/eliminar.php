<?php
/**
 * Eliminar un libro
 * Verifica que no tenga préstamos activos antes de eliminar
 */

// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden eliminar libros
verificarRol(['admin', 'bibliotecario']);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

// Obtener ID del libro
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$con = ObtenerDB();

// Obtener información del libro
$query = $con->prepare("SELECT titulo FROM libros WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$libro = $result->fetch_assoc();

if (!$libro) {
    $query->close();
    $con->close();
    header('Location: index.php');
    exit;
}

$query->close();

// Verificar si tiene préstamos activos
$check = $con->prepare("SELECT COUNT(*) as count FROM prestamos WHERE libro_id = ? AND estado = 'activo'");
$check->bind_param("i", $id);
$check->execute();
$result = $check->get_result();
$prestamos = $result->fetch_assoc();
$check->close();

if ($prestamos['count'] > 0) {
    // No se puede eliminar
    $_SESSION['error_eliminar'] = 'No se puede eliminar el libro "' . $libro['titulo'] . '" porque tiene ' . $prestamos['count'] . ' préstamo(s) activo(s).';
    $con->close();
    header('Location: index.php');
    exit;
}

// Confirmar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = $con->prepare("DELETE FROM libros WHERE id = ?");
    $query->bind_param("i", $id);
    
    if ($query->execute()) {
        $_SESSION['success_eliminar'] = 'El libro "' . $libro['titulo'] . '" ha sido eliminado exitosamente.';
    } else {
        $_SESSION['error_eliminar'] = 'Error al eliminar el libro: ' . $con->error;
    }
    
    $query->close();
    $con->close();
    header('Location: index.php');
    exit;
}

$titulo_pagina = 'Eliminar Libro';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-trash-alt"></i> Confirmar Eliminación</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>Atención:</strong> Esta acción no se puede deshacer.
        </div>
    </div>

    <div class="form-container">
        <div class="confirm-box">
            <div class="confirm-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <h2>¿Está seguro de eliminar este libro?</h2>
            <div class="book-info">
                <p><strong>Título:</strong> <?php echo htmlspecialchars($libro['titulo']); ?></p>
            </div>
            <p class="confirm-text">
                Esta acción eliminará permanentemente el libro de la base de datos.
            </p>
            <div class="form-actions">
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar
                    </button>
                </form>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> No, Cancelar
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$con->close();
include '../includes/footer.php';
?>
