<?php
/**
 * Formulario para registrar un nuevo préstamo
 */

// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$titulo_pagina = 'Nuevo Préstamo';
$error = '';
$success = '';

// Variables para usuarios comunes
$es_usuario_comun = ($usuario_logueado['rol'] === 'usuario');
$libro_id_preseleccionado = $_GET['libro_id'] ?? null;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libro_id = $_POST['libro_id'] ?? null;
    
    // Para usuarios comunes, el usuario_id es el suyo propio
    if ($es_usuario_comun) {
        $usuario_id = $usuario_logueado['usuario_id'];
    } else {
        $usuario_id = $_POST['usuario_id'] ?? null;
    }
    
    $fecha_devolucion = $_POST['fecha_devolucion'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');
    
    // Validaciones
    if (empty($libro_id)) {
        $error = 'Debe seleccionar un libro';
    } elseif (empty($usuario_id)) {
        $error = 'Debe seleccionar un usuario';
    } elseif (empty($fecha_devolucion)) {
        $error = 'Debe especificar la fecha de devolución';
    } else {
        $con = ObtenerDB();
        
        // Verificar que el libro existe y está disponible
        $check_libro = $con->prepare("SELECT estado FROM libros WHERE id = ?");
        $check_libro->bind_param("i", $libro_id);
        $check_libro->execute();
        $result_libro = $check_libro->get_result();
        $libro = $result_libro->fetch_assoc();
        
        if (!$libro) {
            $error = 'El libro seleccionado no existe';
        } elseif ($libro['estado'] !== 'disponible') {
            $error = 'El libro no está disponible para préstamo';
        } else {
            // Verificar que el usuario existe y está activo
            $check_usuario = $con->prepare("SELECT estado FROM usuarios WHERE id = ?");
            $check_usuario->bind_param("i", $usuario_id);
            $check_usuario->execute();
            $result_usuario = $check_usuario->get_result();
            $usuario = $result_usuario->fetch_assoc();
            
            if (!$usuario) {
                $error = 'El usuario seleccionado no existe';
            } elseif ($usuario['estado'] !== 'activo') {
                $error = 'El usuario no está activo';
            } else {
                // Iniciar transacción
                $con->begin_transaction();
                
                try {
                    // Crear el préstamo
                    $query = $con->prepare("INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_devolucion, observaciones, estado) 
                                           VALUES (?, ?, CURDATE(), ?, ?, 'activo')");
                    $query->bind_param("iiss", $libro_id, $usuario_id, $fecha_devolucion, $observaciones);
                    $query->execute();
                    
                    // Actualizar el estado del libro a 'prestado'
                    $update_libro = $con->prepare("UPDATE libros SET estado = 'prestado' WHERE id = ?");
                    $update_libro->bind_param("i", $libro_id);
                    $update_libro->execute();
                    
                    $con->commit();
                    $success = 'Préstamo registrado exitosamente';
                    
                    // Redirigir según el tipo de usuario
                    if ($es_usuario_comun) {
                        header("Location: mis_prestamos.php");
                        exit;
                    }
                    
                    // Limpiar formulario
                    $libro_id = $usuario_id = $fecha_devolucion = $observaciones = '';
                    
                    $query->close();
                    $update_libro->close();
                } catch (Exception $e) {
                    $con->rollback();
                    $error = 'Error al registrar el préstamo: ' . $e->getMessage();
                }
            }
            
            $check_usuario->close();
        }
        
        $check_libro->close();
        $con->close();
    }
}

// Obtener libros disponibles y usuarios activos
$con = ObtenerDB();

$libros_disponibles = $con->query("SELECT id, titulo, autor, isbn FROM libros WHERE estado = 'disponible' ORDER BY titulo ASC");
$usuarios_activos = $con->query("SELECT id, nombre_completo, dni, email FROM usuarios WHERE estado = 'activo' ORDER BY nombre_completo ASC");

$con->close();

// Fecha mínima (mañana) y sugerida (15 días desde hoy)
$fecha_minima = date('Y-m-d', strtotime('+1 day'));
$fecha_sugerida = date('Y-m-d', strtotime('+15 days'));

include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i> <?php echo $es_usuario_comun ? 'Solicitar Préstamo' : 'Nuevo Préstamo'; ?></h1>
        <a href="<?php echo $es_usuario_comun ? 'mis_prestamos.php' : 'index.php'; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="" class="form-modern" id="form-prestamo">
            <div class="form-grid">
                <?php if (!$es_usuario_comun): ?>
                <!-- Campo de usuario solo para admin/bibliotecarios -->
                <div class="form-group">
                    <label for="usuario_id" class="form-label">
                        <i class="fas fa-user"></i> Usuario *
                    </label>
                    <select id="usuario_id" name="usuario_id" class="form-control" required>
                        <option value="">Seleccionar usuario...</option>
                        <?php while ($usuario = $usuarios_activos->fetch_assoc()): ?>
                            <option value="<?php echo $usuario['id']; ?>" 
                                    <?php echo (isset($_POST['usuario_id']) && $_POST['usuario_id'] == $usuario['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($usuario['nombre_completo']); ?> 
                                (<?php echo htmlspecialchars($usuario['dni']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-help">Solo se muestran usuarios activos</small>
                </div>
                <?php else: ?>
                <!-- Mostrar información del usuario para usuarios comunes -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Solicitante
                    </label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuario_logueado['nombre']); ?>" readonly>
                    <small class="form-help">Este préstamo será registrado a tu nombre</small>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="libro_id" class="form-label">
                        <i class="fas fa-book"></i> Libro *
                    </label>
                    <select id="libro_id" name="libro_id" class="form-control" required>
                        <option value="">Seleccionar libro...</option>
                        <?php while ($libro = $libros_disponibles->fetch_assoc()): ?>
                            <option value="<?php echo $libro['id']; ?>"
                                    <?php echo (isset($_POST['libro_id']) && $_POST['libro_id'] == $libro['id']) || ($libro_id_preseleccionado == $libro['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($libro['titulo']); ?> 
                                - <?php echo htmlspecialchars($libro['autor']); ?>
                                (<?php echo htmlspecialchars($libro['isbn']); ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-help">Solo se muestran libros disponibles</small>
                </div>

                <div class="form-group">
                    <label for="fecha_devolucion" class="form-label">
                        <i class="fas fa-calendar-alt"></i> Fecha de Devolución *
                    </label>
                    <input type="date" 
                           id="fecha_devolucion" 
                           name="fecha_devolucion" 
                           class="form-control" 
                           required 
                           min="<?php echo $fecha_minima; ?>"
                           value="<?php echo $_POST['fecha_devolucion'] ?? $fecha_sugerida; ?>">
                    <small class="form-help">Sugerencia: 15 días desde hoy</small>
                </div>
            </div>

            <div class="form-group">
                <label for="observaciones" class="form-label">
                    <i class="fas fa-comment"></i> Observaciones
                </label>
                <textarea id="observaciones" 
                          name="observaciones" 
                          class="form-control" 
                          rows="3" 
                          placeholder="Notas adicionales sobre el préstamo..."><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Información importante:</strong>
                    <ul>
                        <?php if ($es_usuario_comun): ?>
                            <li>Una vez solicitado el préstamo, deberás recoger el libro en la biblioteca</li>
                            <li>Se recomienda devolver el libro en la fecha indicada</li>
                            <li>Puedes ver el estado de tus préstamos en "Mis Préstamos"</li>
                        <?php else: ?>
                            <li>El libro quedará marcado como "prestado" automáticamente</li>
                            <li>El usuario recibirá una notificación (si está configurado)</li>
                            <li>Se recomienda un período de préstamo de 15 días</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $es_usuario_comun ? 'Solicitar Préstamo' : 'Registrar Préstamo'; ?>
                </button>
                <a href="<?php echo $es_usuario_comun ? 'mis_prestamos.php' : 'index.php'; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
