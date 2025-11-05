<?php
// Incluir configuración
require_once __DIR__ . '/../config/config.php';

$titulo_pagina = 'Gestión de Libros';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-book-open"></i> Gestión de Libros</h1>
        <a href="crear.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Libro
        </a>
    </div>

    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="buscar-libro" placeholder="Buscar por título, autor o ISBN...">
        </div>
        <div class="filter-buttons">
            <select id="filtro-categoria" class="form-select">
                <option value="">Todas las categorías</option>
                <option value="ficcion">Ficción</option>
                <option value="no-ficcion">No Ficción</option>
                <option value="ciencia">Ciencia</option>
                <option value="historia">Historia</option>
                <option value="tecnologia">Tecnología</option>
            </select>
            <select id="filtro-disponibilidad" class="form-select">
                <option value="">Todos</option>
                <option value="disponible">Disponibles</option>
                <option value="prestado">Prestados</option>
            </select>
        </div>
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
                <!-- Datos de ejemplo (sin backend) -->
                <tr>
                    <td>978-3-16-148410-0</td>
                    <td>Cien Años de Soledad</td>
                    <td>Gabriel García Márquez</td>
                    <td><span class="badge badge-info">Ficción</span></td>
                    <td><span class="badge badge-success">Disponible</span></td>
                    <td>3 de 5</td>
                    <td class="table-actions">
                        <a href="editar.php?id=1" class="btn-icon btn-icon-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="eliminar.php?id=1" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este libro?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>978-0-06-112008-4</td>
                    <td>1984</td>
                    <td>George Orwell</td>
                    <td><span class="badge badge-info">Ficción</span></td>
                    <td><span class="badge badge-warning">Prestado</span></td>
                    <td>0 de 3</td>
                    <td class="table-actions">
                        <a href="editar.php?id=2" class="btn-icon btn-icon-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="eliminar.php?id=2" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este libro?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>978-0-7432-7356-5</td>
                    <td>Sapiens</td>
                    <td>Yuval Noah Harari</td>
                    <td><span class="badge badge-success">Historia</span></td>
                    <td><span class="badge badge-success">Disponible</span></td>
                    <td>2 de 4</td>
                    <td class="table-actions">
                        <a href="editar.php?id=3" class="btn-icon btn-icon-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="eliminar.php?id=3" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este libro?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
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