<?php
// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden gestionar préstamos
verificarRol(['admin', 'bibliotecario']);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$titulo_pagina = 'Gestión de Préstamos';

// Obtener préstamos de la base de datos
$con = ObtenerDB();

// Filtros
$busqueda = $_GET['busqueda'] ?? '';
$estado = $_GET['estado'] ?? '';

// Construir query con filtros
$query = "SELECT p.*, 
          l.titulo as libro_titulo, 
          l.autor as libro_autor, 
          l.isbn as libro_isbn,
          u.nombre_completo as usuario_nombre,
          u.email as usuario_email,
          u.dni as usuario_dni,
          DATEDIFF(CURDATE(), p.fecha_devolucion) as dias_retraso
          FROM prestamos p
          INNER JOIN libros l ON p.libro_id = l.id
          INNER JOIN usuarios u ON p.usuario_id = u.id
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $query .= " AND (u.nombre_completo LIKE ? OR l.titulo LIKE ? OR l.autor LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "sss";
}

if (!empty($estado)) {
    if ($estado === 'vencido') {
        $query .= " AND p.estado = 'activo' AND p.fecha_devolucion < CURDATE()";
    } else {
        $query .= " AND p.estado = ?";
        $params[] = $estado;
        $types .= "s";
    }
}

$query .= " ORDER BY p.fecha_prestamo DESC";

$stmt = $con->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$prestamos = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$con->close();

include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-exchange-alt"></i> Gestión de Préstamos</h1>
        <div class="header-actions">
            <a href="nuevo.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Préstamo
            </a>
            <a href="historial.php" class="btn btn-secondary">
                <i class="fas fa-history"></i> Ver Historial
            </a>
        </div>
    </div>

    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="busqueda" id="buscar-prestamo" 
                       placeholder="Buscar por usuario o libro..."
                       value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
            <div class="filter-buttons">
                <select name="estado" id="filtro-estado-prestamo" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activos</option>
                    <option value="vencido" <?php echo $estado === 'vencido' ? 'selected' : ''; ?>>Vencidos</option>
                    <option value="devuelto" <?php echo $estado === 'devuelto' ? 'selected' : ''; ?>>Devueltos</option>
                </select>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <?php if (!empty($busqueda) || !empty($estado)): ?>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Libro</th>
                    <th>Fecha Préstamo</th>
                    <th>Fecha Devolución</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($prestamos) > 0): ?>
                    <?php foreach ($prestamos as $prestamo): ?>
                        <?php
                        // Determinar el estado real del préstamo
                        $es_vencido = $prestamo['estado'] === 'vencido' && $prestamo['dias_retraso'] > 0;
                        $estado_mostrar = $prestamo['estado'];
                        $row_class = '';
                        
                        if ($es_vencido) {
                            $row_class = 'row-warning';
                        }
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
                                <?php if ($prestamo['estado'] === 'devuelto'): ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Devuelto
                                    </span>
                                    <?php if (!empty($prestamo['fecha_dev_real'])): ?>
                                        <br><small class="text-muted"><?php echo date('d/m/Y', strtotime($prestamo['fecha_dev_real'])); ?></small>
                                    <?php endif; ?>
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
                            <td class="table-actions">
                                <?php if ($prestamo['estado'] === 'activo'): ?>
                                    <a href="devolver.php?id=<?php echo $prestamo['id']; ?>" 
                                       class="btn-icon btn-icon-success" 
                                       title="Marcar como devuelto">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No se encontraron préstamos</p>
                            <?php if (!empty($busqueda) || !empty($estado)): ?>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar filtros
                                </a>
                            <?php else: ?>
                                <a href="nuevo.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Registrar primer préstamo
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <button class="btn btn-secondary" disabled><i class="fas fa-chevron-left"></i> Anterior</button>
        <span class="pagination-info">Página 1 de 1</span>
        <button class="btn btn-secondary" disabled>Siguiente <i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<?php include '../includes/footer.php'; ?>