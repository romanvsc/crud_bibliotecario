<?php
/**
 * Vista de libros para usuarios comunes
 * Solo visualización y opción de solicitar préstamo
 */

// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo usuarios comunes pueden acceder a esta vista
if ($usuario_logueado['rol'] !== 'usuario') {
    header("Location: ../libros/index.php");
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$titulo_pagina = 'Catálogo de Libros';

// Obtener libros de la base de datos
$con = ObtenerDB();

// Filtros
$busqueda = $_GET['busqueda'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$estado = $_GET['estado'] ?? '';

// Construir query con filtros
$query = "SELECT * FROM libros WHERE 1=1";
$params = [];
$types = "";

if (!empty($busqueda)) {
    $query .= " AND (titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?)";
    $busqueda_param = "%$busqueda%";
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $params[] = $busqueda_param;
    $types .= "sss";
}

if (!empty($categoria)) {
    $query .= " AND categoria = ?";
    $params[] = $categoria;
    $types .= "s";
}

if (!empty($estado)) {
    $query .= " AND estado = ?";
    $params[] = $estado;
    $types .= "s";
}

$query .= " ORDER BY titulo ASC";

$stmt = $con->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$libros = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$con->close();

include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-book-open"></i> Catálogo de Libros</h1>
            <p class="subtitle">Explora nuestra colección de libros disponibles</p>
        </div>
    </div>

    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="busqueda" 
                       placeholder="Buscar por título, autor o ISBN..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
            <div class="filter-buttons">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <option value="ficcion" <?php echo $categoria === 'ficcion' ? 'selected' : ''; ?>>Ficción</option>
                    <option value="no-ficcion" <?php echo $categoria === 'no-ficcion' ? 'selected' : ''; ?>>No Ficción</option>
                    <option value="ciencia" <?php echo $categoria === 'ciencia' ? 'selected' : ''; ?>>Ciencia</option>
                    <option value="historia" <?php echo $categoria === 'historia' ? 'selected' : ''; ?>>Historia</option>
                    <option value="tecnologia" <?php echo $categoria === 'tecnologia' ? 'selected' : ''; ?>>Tecnología</option>
                    <option value="literatura" <?php echo $categoria === 'literatura' ? 'selected' : ''; ?>>Literatura</option>
                    <option value="arte" <?php echo $categoria === 'arte' ? 'selected' : ''; ?>>Arte</option>
                </select>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="disponible" <?php echo $estado === 'disponible' ? 'selected' : ''; ?>>Disponibles</option>
                    <option value="prestado" <?php echo $estado === 'prestado' ? 'selected' : ''; ?>>Prestados</option>
                </select>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <?php if (!empty($busqueda) || !empty($categoria) || !empty($estado)): ?>
                    <a href="ver.php" class="btn btn-secondary">
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
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Categoría</th>
                    <th>Año</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($libros) > 0): ?>
                    <?php foreach ($libros as $libro): ?>
                    <tr>
                        <td>
                            <div class="book-cell">
                                <i class="fas fa-book"></i>
                                <div>
                                    <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong>
                                    <?php if (!empty($libro['isbn'])): ?>
                                        <br><small class="text-muted">ISBN: <?php echo htmlspecialchars($libro['isbn']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                        <td>
                            <?php if (!empty($libro['categoria'])): ?>
                                <span class="badge badge-info"><?php echo ucfirst(htmlspecialchars($libro['categoria'])); ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Sin categoría</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($libro['anio']) ? htmlspecialchars($libro['anio']) : 'N/A'; ?></td>
                        <td>
                            <?php if ($libro['estado'] === 'disponible'): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Disponible
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-hand-holding"></i> Prestado
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <?php if ($libro['estado'] === 'disponible'): ?>
                                <a href="../prestamos/nuevo.php?libro_id=<?php echo $libro['id']; ?>" 
                                   class="btn btn-sm btn-primary" title="Solicitar préstamo">
                                    <i class="fas fa-hand-holding"></i> Solicitar
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No disponible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <p>No se encontraron libros</p>
                            <?php if (!empty($busqueda) || !empty($categoria) || !empty($estado)): ?>
                                <a href="ver.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar filtros
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
        <span class="pagination-info">Mostrando <?php echo count($libros); ?> libro(s)</span>
        <button class="btn btn-secondary" disabled>Siguiente <i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
