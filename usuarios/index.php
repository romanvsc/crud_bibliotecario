<?php
// Incluir configuración
require_once __DIR__ . '/../config/config.php';

$titulo_pagina = 'Gestión de Usuarios';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-users"></i> Gestión de Usuarios</h1>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </a>
    </div>

    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="buscar-usuario" placeholder="Buscar por nombre, DNI o correo...">
        </div>
        <div class="filter-buttons">
            <select id="filtro-tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="estudiante">Estudiante</option>
                <option value="profesor">Profesor</option>
                <option value="externo">Externo</option>
            </select>
            <select id="filtro-estado" class="form-select">
                <option value="">Todos</option>
                <option value="activo">Activos</option>
                <option value="inactivo">Inactivos</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-usuarios">
                <?php
                require_once __DIR__ . '/../obtenerBaseDeDatos.php';
                $con = ObtenerDB();
                
                // Obtener parámetros de filtro
                $busqueda = $_GET['busqueda'] ?? '';
                $tipo = $_GET['tipo'] ?? '';
                $estado = $_GET['estado'] ?? '';
                
                // Construir consulta con filtros
                $query = "SELECT * FROM usuarios WHERE 1=1";
                $params = [];
                $types = '';
                
                if (!empty($busqueda)) {
                    $query .= " AND (nombre_completo LIKE ? OR dni LIKE ? OR email LIKE ?)";
                    $busqueda_param = "%$busqueda%";
                    $params[] = $busqueda_param;
                    $params[] = $busqueda_param;
                    $params[] = $busqueda_param;
                    $types .= 'sss';
                }
                
                if (!empty($tipo)) {
                    $query .= " AND tipo_usuario = ?";
                    $params[] = $tipo;
                    $types .= 's';
                }
                
                if (!empty($estado)) {
                    $query .= " AND estado = ?";
                    $params[] = $estado;
                    $types .= 's';
                }
                
                $query .= " ORDER BY nombre_completo ASC";
                
                $stmt = $con->prepare($query);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($usuario = $result->fetch_assoc()) {
                        // Badge para tipo
                        $badge_tipo_class = match($usuario['tipo_usuario']) {
                            'estudiante' => 'badge-primary',
                            'profesor' => 'badge-success',
                            'externo' => 'badge-warning',
                            default => 'badge-secondary'
                        };
                        
                        // Badge para estado
                        $badge_estado_class = $usuario['estado'] === 'activo' ? 'badge-success' : 'badge-danger';
                        $estado_texto = ucfirst($usuario['estado']);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($usuario['dni']) ?></td>
                            <td><?= htmlspecialchars($usuario['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                            <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                            <td><span class="badge <?= $badge_tipo_class ?>"><?= ucfirst($usuario['tipo_usuario']) ?></span></td>
                            <td><span class="badge <?= $badge_estado_class ?>"><?= $estado_texto ?></span></td>
                            <td class="table-actions">
                                <a href="detalle.php?id=<?= $usuario['id'] ?>" class="btn-icon btn-icon-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="editar.php?id=<?= $usuario['id'] ?>" class="btn-icon btn-icon-primary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="eliminar.php?id=<?= $usuario['id'] ?>" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="7" style="text-align: center;">No se encontraron usuarios</td></tr>';
                }
                
                $stmt->close();
                $con->close();
                ?>
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