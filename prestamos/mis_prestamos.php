<?php
// Solo usuarios comunes pueden acceder
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo usuarios con rol 'usuario' pueden acceder a esta página
if ($usuario_logueado['rol'] !== 'usuario') {
    header("Location: ../dashboard.php");
    exit;
}

require_once __DIR__ . '/../obtenerBaseDeDatos.php';
$con = ObtenerDB();

// Obtener el usuario_id del usuario logueado (vinculado con tabla usuarios)
$usuario_sistema_id = $usuario_logueado['id'];
$usuario_id = $usuario_logueado['usuario_id'];

if (!$usuario_id) {
    die("Error: Este usuario no está vinculado correctamente con un perfil de biblioteca.");
}

$titulo_pagina = 'Mis Préstamos';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-book-reader"></i> Mis Préstamos</h1>
        <a href="../prestamos/nuevo.php?usuario_id=<?= $usuario_id ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Solicitar Préstamo
        </a>
    </div>

    <div class="filters-section">
        <div class="filter-buttons">
            <button class="btn btn-filter active" data-estado="">Todos</button>
            <button class="btn btn-filter" data-estado="activo">Activos</button>
            <button class="btn btn-filter" data-estado="devuelto">Devueltos</button>
            <button class="btn btn-filter" data-estado="vencido">Vencidos</button>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Libro</th>
                    <th>Autor</th>
                    <th>Fecha Préstamo</th>
                    <th>Fecha Devolución</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-prestamos">
                <?php
                // Consultar préstamos del usuario
                $query = "SELECT p.*, l.titulo, l.autor, l.isbn 
                         FROM prestamos p 
                         INNER JOIN libros l ON p.libro_id = l.id 
                         WHERE p.usuario_id = ? 
                         ORDER BY p.fecha_prestamo DESC";
                
                $stmt = $con->prepare($query);
                $stmt->bind_param("i", $usuario_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    while ($prestamo = $result->fetch_assoc()) {
                        // Determinar clase y texto del badge según estado
                        $badge_class = '';
                        $estado_texto = '';
                        
                        switch ($prestamo['estado']) {
                            case 'activo':
                                $badge_class = 'badge-info';
                                $estado_texto = 'Activo';
                                break;
                            case 'devuelto':
                                $badge_class = 'badge-success';
                                $estado_texto = 'Devuelto';
                                break;
                            case 'vencido':
                                $badge_class = 'badge-danger';
                                $estado_texto = 'Vencido';
                                break;
                        }
                        
                        $fecha_prestamo = date('d/m/Y', strtotime($prestamo['fecha_prestamo']));
                        $fecha_devolucion = date('d/m/Y', strtotime($prestamo['fecha_devolucion']));
                        ?>
                        <tr data-estado="<?= $prestamo['estado'] ?>">
                            <td><?= htmlspecialchars($prestamo['titulo']) ?></td>
                            <td><?= htmlspecialchars($prestamo['autor']) ?></td>
                            <td><?= $fecha_prestamo ?></td>
                            <td><?= $fecha_devolucion ?></td>
                            <td><span class="badge <?= $badge_class ?>"><?= $estado_texto ?></span></td>
                            <td class="table-actions">
                                <?php if ($prestamo['estado'] === 'activo'): ?>
                                    <button class="btn btn-sm btn-info" 
                                            onclick="alert('Para devolver este libro, por favor acérquese a la biblioteca.')">
                                        <i class="fas fa-info-circle"></i> Devolver
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="6" style="text-align: center;">No tienes préstamos registrados</td></tr>';
                }
                
                $stmt->close();
                $con->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Filtrar por estado
document.querySelectorAll('.btn-filter').forEach(btn => {
    btn.addEventListener('click', function() {
        const estado = this.dataset.estado;
        
        // Actualizar botones activos
        document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Filtrar filas
        document.querySelectorAll('#tabla-prestamos tr').forEach(row => {
            if (!estado || row.dataset.estado === estado) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
