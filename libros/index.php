<?php
// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$titulo_pagina = 'Gestión de Libros';

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

$query .= " ORDER BY created_at DESC";

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
        <h1><i class="fas fa-book-open"></i> Gestión de Libros</h1>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Libro
        </a>
    </div>

    <?php if (isset($_SESSION['error_eliminar'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['error_eliminar']); ?></span>
        </div>
        <?php unset($_SESSION['error_eliminar']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_eliminar'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['success_eliminar']); ?></span>
        </div>
        <?php unset($_SESSION['success_eliminar']); ?>
    <?php endif; ?>

    <div class="filters-section">
        <form method="GET" action="" class="filters-form">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="busqueda" id="buscar-libro" 
                       placeholder="Buscar por título, autor o ISBN..." 
                       value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
            <div class="filter-buttons">
                <select name="categoria" id="filtro-categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <option value = "Ciencia ficción" <?php echo $categoria === 'Ciencia ficción' ? 'selected' : ''; ?>>Ciencia ficción</option>
                    <option value = "Clásico" <?php echo $categoria === 'Clásico' ? 'selected' : ''; ?>>Clásico</option>
                    <option value = "Drama" <?php echo $categoria === 'Drama' ? 'selected' : ''; ?>>Drama</option>
                    <option value = "Drama psicológico" <?php echo $categoria === 'Drama psicológico' ? 'selected' : ''; ?>>Drama psicológico</option>
                    <option value = "Experimental" <?php echo $categoria === 'Experimental' ? 'selected' : ''; ?>>Experimental</option>
                    <option value = "Fantasía" <?php echo $categoria === 'Fantasía' ? 'selected' : ''; ?>>Fantasía</option>
                    <option value = "Ficción" <?php echo $categoria === 'Ficción' ? 'selected' : ''; ?>>Ficción</option>
                    <option value = "Ficción psicológica" <?php echo $categoria === 'Ficción psicológica' ? 'selected' : ''; ?>>Ficción psicológica</option>
                    <option value = "Misterio" <?php echo $categoria === 'Misterio' ? 'selected' : ''; ?>>Misterio</option>
                    <option value = "Realismo mágico" <?php echo $categoria === 'Realismo mágico' ? 'selected' : ''; ?>>Realismo mágico</option>
                    <option value = "Tomance" <?php echo $categoria === 'Tomance' ? 'selected' : ''; ?>>Tomance</option>
                    <option value = "Thriller" <?php echo $categoria === 'Thriller' ? 'selected' : ''; ?>>Thriller</option>
                </select>
                <select name="estado" id="filtro-disponibilidad" class="form-select">
                    <option value="">Todos</option>
                    <option value="disponible" <?php echo $estado === 'disponible' ? 'selected' : ''; ?>>Disponibles</option>
                    <option value="prestado" <?php echo $estado === 'prestado' ? 'selected' : ''; ?>>Prestados</option>
                </select>
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <?php if (!empty($busqueda) || !empty($categoria) || !empty($estado)): ?>
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
                    <th>ISBN</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Disponibles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-libros">
                <?php if (count($libros) > 0): ?>
                    <?php foreach ($libros as $libro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($libro['isbn']); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($libro['titulo']); ?></strong>
                            <?php if (!empty($libro['anio'])): ?>
                                <br><small class="text-muted">(<?php echo $libro['anio']; ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                        <td>
                            <?php if (!empty($libro['categoria'])): ?>
                                <span class="badge badge-info"><?php echo ucfirst(htmlspecialchars($libro['categoria'])); ?></span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Sin categoría</span>
                            <?php endif; ?>
                        </td>
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
                        <td><?php echo htmlspecialchars($libro['editorial'] ?? 'N/A'); ?></td>
                        <td class="table-actions">
                            <a href="editar.php?id=<?php echo $libro['id']; ?>" 
                               class="btn-icon btn-icon-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="eliminar.php?id=<?php echo $libro['id']; ?>" 
                               class="btn-icon btn-icon-danger" title="Eliminar" 
                               onclick="return confirm('¿Está seguro de eliminar este libro?\n\nTítulo: <?php echo addslashes(htmlspecialchars($libro['titulo'])); ?>')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <p>No se encontraron libros</p>
                            <?php if (!empty($busqueda) || !empty($categoria) || !empty($estado)): ?>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar filtros
                                </a>
                            <?php else: ?>
                                <a href="crear.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Agregar primer libro
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