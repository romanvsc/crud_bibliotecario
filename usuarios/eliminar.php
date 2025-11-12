<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden eliminar usuarios
verificarRol(['admin', 'bibliotecario']);

require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

$con = ObtenerDB();

// Obtener datos del usuario
$stmt = $con->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['mensaje'] = "Usuario no encontrado";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: index.php");
    exit;
}

$usuario = $result->fetch_assoc();
$stmt->close();

// Verificar préstamos activos
$stmt = $con->prepare("SELECT COUNT(*) as count FROM prestamos WHERE usuario_id = ? AND estado = 'activo'");
$stmt->bind_param("i", $id);
$stmt->execute();
$prestamos_activos = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    if ($prestamos_activos > 0) {
        $_SESSION['mensaje'] = "No se puede eliminar el usuario porque tiene préstamos activos";
        $_SESSION['tipo_mensaje'] = "error";
    } else {
        $stmt = $con->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Usuario eliminado exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
            $stmt->close();
            $con->close();
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['mensaje'] = "Error al eliminar el usuario: " . $con->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        $stmt->close();
    }
}

$con->close();

$titulo_pagina = 'Eliminar Usuario';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-user-times"></i> Eliminar Usuario</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?>">
            <i class="fas fa-<?= $_SESSION['tipo_mensaje'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <p><?= htmlspecialchars($_SESSION['mensaje']) ?></p>
        </div>
        <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        endif; 
    ?>

    <div class="devolver-container">
        <?php if ($prestamos_activos > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>No se puede eliminar este usuario</strong>
                    <p>El usuario tiene <?= $prestamos_activos ?> préstamo(s) activo(s). Debe devolver todos los libros antes de poder eliminar el usuario.</p>
                </div>
            </div>
            
            <div class="devolver-info">
                <div class="info-group">
                    <h3>Información del Usuario</h3>
                    <div class="info-item">
                        <strong>DNI:</strong>
                        <span><?= htmlspecialchars($usuario['dni']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Nombre:</strong>
                        <span><?= htmlspecialchars($usuario['nombre_completo']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?= htmlspecialchars($usuario['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Préstamos Activos:</strong>
                        <span class="badge badge-danger"><?= $prestamos_activos ?></span>
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="detalle.php?id=<?= $id ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Ver Detalles
                </a>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
            </div>
        <?php else: ?>
            <div class="confirmation-box">
                <h3><i class="fas fa-exclamation-triangle"></i> ¿Está seguro de eliminar este usuario?</h3>
                <p>Esta acción no se puede deshacer. Se eliminará permanentemente:</p>
            </div>

            <div class="devolver-info">
                <div class="info-group">
                    <h3>Información del Usuario</h3>
                    <div class="info-item">
                        <strong>DNI:</strong>
                        <span><?= htmlspecialchars($usuario['dni']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Nombre:</strong>
                        <span><?= htmlspecialchars($usuario['nombre_completo']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Email:</strong>
                        <span><?= htmlspecialchars($usuario['email']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Teléfono:</strong>
                        <span><?= htmlspecialchars($usuario['telefono']) ?></span>
                    </div>
                    <div class="info-item">
                        <strong>Tipo:</strong>
                        <span class="badge <?= match($usuario['tipo_usuario']) {
                            'estudiante' => 'badge-primary',
                            'profesor' => 'badge-success',
                            'externo' => 'badge-warning',
                            default => 'badge-secondary'
                        } ?>">
                            <?= ucfirst($usuario['tipo_usuario']) ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Estado:</strong>
                        <span class="badge <?= $usuario['estado'] === 'activo' ? 'badge-success' : 'badge-danger' ?>">
                            <?= ucfirst($usuario['estado']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <form method="POST" class="actions">
                <button type="submit" name="confirmar" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Sí, Eliminar Usuario
                </button>
                <a href="index.php" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.btn-danger {
    background: var(--color-error);
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: var(--radio-md);
    cursor: pointer;
    font-size: 1em;
    transition: all 0.3s ease;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}
</style>

<?php include '../includes/footer.php'; ?>
