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
                <!-- Datos de ejemplo -->
                <tr>
                    <td>12345678A</td>
                    <td>Juan Pérez García</td>
                    <td>juan.perez@email.com</td>
                    <td>612 345 678</td>
                    <td><span class="badge badge-primary">Estudiante</span></td>
                    <td><span class="badge badge-success">Activo</span></td>
                    <td class="table-actions">
                        <a href="detalle.php?id=1" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="editar.php?id=1" class="btn-icon btn-icon-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="eliminar.php?id=1" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>87654321B</td>
                    <td>María López Fernández</td>
                    <td>maria.lopez@email.com</td>
                    <td>623 456 789</td>
                    <td><span class="badge badge-success">Profesor</span></td>
                    <td><span class="badge badge-success">Activo</span></td>
                    <td class="table-actions">
                        <a href="detalle.php?id=2" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="editar.php?id=2" class="btn-icon btn-icon-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="eliminar.php?id=2" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este usuario?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>11223344C</td>
                    <td>Carlos Martínez Ruiz</td>
                    <td>carlos.martinez@email.com</td>
                    <td>634 567 890</td>
                    <td><span class="badge badge-warning">Externo</span></td>
                    <td><span class="badge badge-danger">Inactivo</span></td>
                    <td class="table-actions">
                        <a href="detalle.php?id=3" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="editar.php?id=3" class="btn-icon btn-icon-primary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="eliminar.php?id=3" class="btn-icon btn-icon-danger" title="Eliminar" onclick="return confirm('¿Está seguro de eliminar este usuario?')">
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