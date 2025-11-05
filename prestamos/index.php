<?php
// Incluir configuración
require_once __DIR__ . '/../config/config.php';

$titulo_pagina = 'Gestión de Préstamos';
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
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="buscar-prestamo" placeholder="Buscar por usuario o libro...">
        </div>
        <div class="filter-buttons">
            <select id="filtro-estado-prestamo" class="form-select">
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="vencido">Vencidos</option>
                <option value="devuelto">Devueltos</option>
            </select>
            <button class="btn btn-secondary" id="btn-export">
                <i class="fas fa-download"></i> Exportar
            </button>
        </div>
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
            <tbody id="tabla-prestamos">
                <!-- Datos de ejemplo -->
                <tr>
                    <td>#001</td>
                    <td>
                        <div class="user-cell">
                            <i class="fas fa-user-circle"></i>
                            <span>Juan Pérez García</span>
                        </div>
                    </td>
                    <td>
                        <div class="book-cell">
                            <i class="fas fa-book"></i>
                            <span>Cien Años de Soledad</span>
                        </div>
                    </td>
                    <td>15/10/2025</td>
                    <td>29/10/2025</td>
                    <td><span class="badge badge-success">Devuelto</span></td>
                    <td class="table-actions">
                        <a href="#" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>#002</td>
                    <td>
                        <div class="user-cell">
                            <i class="fas fa-user-circle"></i>
                            <span>María López Fernández</span>
                        </div>
                    </td>
                    <td>
                        <div class="book-cell">
                            <i class="fas fa-book"></i>
                            <span>1984</span>
                        </div>
                    </td>
                    <td>01/11/2025</td>
                    <td>15/11/2025</td>
                    <td><span class="badge badge-primary">Activo</span></td>
                    <td class="table-actions">
                        <a href="#" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="devolver.php?id=2" class="btn-icon btn-icon-success" title="Marcar como devuelto">
                            <i class="fas fa-check"></i>
                        </a>
                    </td>
                </tr>
                <tr class="row-warning">
                    <td>#003</td>
                    <td>
                        <div class="user-cell">
                            <i class="fas fa-user-circle"></i>
                            <span>Carlos Martínez Ruiz</span>
                        </div>
                    </td>
                    <td>
                        <div class="book-cell">
                            <i class="fas fa-book"></i>
                            <span>Sapiens</span>
                        </div>
                    </td>
                    <td>20/10/2025</td>
                    <td>03/11/2025</td>
                    <td><span class="badge badge-danger">Vencido</span></td>
                    <td class="table-actions">
                        <a href="#" class="btn-icon btn-icon-info" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="devolver.php?id=3" class="btn-icon btn-icon-success" title="Marcar como devuelto">
                            <i class="fas fa-check"></i>
                        </a>
                        <a href="#" class="btn-icon btn-icon-warning" title="Enviar recordatorio">
                            <i class="fas fa-envelope"></i>
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