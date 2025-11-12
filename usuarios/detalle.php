<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden ver detalles de usuarios
verificarRol(['admin', 'bibliotecario']);

require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

$con = ObtenerDB();

// Obtener información del usuario
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

// Obtener estadísticas de préstamos
$stmt = $con->prepare("SELECT 
    COUNT(*) as total_prestamos,
    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as prestamos_activos,
    COUNT(CASE WHEN estado = 'devuelto' THEN 1 END) as prestamos_devueltos,
    COUNT(CASE WHEN estado = 'activo' AND fecha_devolucion < CURDATE() THEN 1 END) as prestamos_vencidos
    FROM prestamos WHERE usuario_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener préstamos activos
$stmt = $con->prepare("SELECT p.*, l.titulo, l.autor, l.isbn 
                       FROM prestamos p 
                       JOIN libros l ON p.libro_id = l.id 
                       WHERE p.usuario_id = ? AND p.estado = 'activo'
                       ORDER BY p.fecha_prestamo DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$prestamos_activos = $stmt->get_result();
$stmt->close();

$con->close();

$titulo_pagina = 'Detalle de Usuario';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-user"></i> Detalle de Usuario</h1>
        <div>
            <a href="editar.php?id=<?= $id ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="detail-container">
        <!-- Información Personal -->
        <div class="detail-card">
            <div class="card-header">
                <h2><i class="fas fa-id-card"></i> Información Personal</h2>
                <span class="badge <?= $usuario['estado'] === 'activo' ? 'badge-success' : 'badge-danger' ?>">
                    <?= ucfirst($usuario['estado']) ?>
                </span>
            </div>
            <div class="card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <strong><i class="fas fa-fingerprint"></i> DNI:</strong>
                        <span><?= htmlspecialchars($usuario['dni']) ?></span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-user"></i> Nombre Completo:</strong>
                        <span><?= htmlspecialchars($usuario['nombre_completo']) ?></span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-envelope"></i> Correo:</strong>
                        <span><?= htmlspecialchars($usuario['email']) ?></span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-phone"></i> Teléfono:</strong>
                        <span><?= htmlspecialchars($usuario['telefono']) ?></span>
                    </div>
                    <div class="detail-item full-width">
                        <strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong>
                        <span><?= htmlspecialchars($usuario['direccion'] ?: 'No especificada') ?></span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-user-tag"></i> Tipo:</strong>
                        <span class="badge <?= match($usuario['tipo_usuario']) {
                            'estudiante' => 'badge-primary',
                            'profesor' => 'badge-success',
                            'externo' => 'badge-warning',
                            default => 'badge-secondary'
                        } ?>">
                            <?= ucfirst($usuario['tipo_usuario']) ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-calendar-plus"></i> Fecha de Registro:</strong>
                        <span><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Préstamos -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_prestamos'] ?></h3>
                    <p>Total Préstamos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['prestamos_activos'] ?></h3>
                    <p>Préstamos Activos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['prestamos_devueltos'] ?></h3>
                    <p>Devueltos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['prestamos_vencidos'] ?></h3>
                    <p>Vencidos</p>
                </div>
            </div>
        </div>

        <!-- Préstamos Activos -->
        <?php if ($prestamos_activos->num_rows > 0): ?>
        <div class="detail-card">
            <div class="card-header">
                <h2><i class="fas fa-book-reader"></i> Préstamos Activos</h2>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Libro</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Devolución</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($prestamo = $prestamos_activos->fetch_assoc()): 
                                $vencido = strtotime($prestamo['fecha_devolucion']) < time();
                            ?>
                            <tr <?= $vencido ? 'class="row-warning"' : '' ?>>
                                <td>
                                    <div class="book-cell">
                                        <strong><?= htmlspecialchars($prestamo['titulo']) ?></strong>
                                        <small><?= htmlspecialchars($prestamo['autor']) ?></small>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($prestamo['fecha_prestamo'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($prestamo['fecha_devolucion'])) ?></td>
                                <td>
                                    <?php if ($vencido): ?>
                                        <span class="badge badge-danger">Vencido</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Activo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="table-actions">
                                    <a href="../prestamos/devolver.php?id=<?= $prestamo['id'] ?>" class="btn-icon btn-icon-success" title="Devolver">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.detail-container {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.detail-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    background: var(--color-fondo-claro);
    padding: 20px;
    border-bottom: 2px solid var(--color-acento);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    color: var(--color-acento);
    font-size: 1.3em;
}

.card-body {
    padding: 30px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item strong {
    color: #555;
    font-weight: 600;
}

.detail-item strong i {
    margin-right: 8px;
    color: var(--color-acento);
}

.detail-item span {
    color: #333;
    font-size: 1.05em;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.bg-primary { background: var(--color-acento); }
.bg-success { background: var(--color-exito); }
.bg-warning { background: var(--color-advertencia); }
.bg-danger { background: var(--color-error); }
</style>

<?php include '../includes/footer.php'; ?>
