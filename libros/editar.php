<?php
/**
 * Formulario para editar un libro existente
 */

// Incluir autenticación
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden editar libros
verificarRol(['admin', 'bibliotecario']);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

$titulo_pagina = 'Editar Libro';
$error = '';
$success = '';

// Obtener ID del libro
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$con = ObtenerDB();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $editorial = trim($_POST['editorial'] ?? '');
    $anio = trim($_POST['anio'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado = $_POST['estado'] ?? 'disponible';
    
    // Validaciones
    if (empty($titulo)) {
        $error = 'El título es obligatorio';
    } elseif (empty($autor)) {
        $error = 'El autor es obligatorio';
    } elseif (empty($isbn)) {
        $error = 'El ISBN es obligatorio';
    } else {
        // Verificar que el ISBN no esté duplicado (excepto para este libro)
        $check = $con->prepare("SELECT id FROM libros WHERE isbn = ? AND id != ?");
        $check->bind_param("si", $isbn, $id);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = 'Ya existe otro libro con ese ISBN';
        } else {
            // Actualizar el libro
            $query = $con->prepare("UPDATE libros SET 
                                    titulo = ?, 
                                    autor = ?, 
                                    isbn = ?, 
                                    editorial = ?, 
                                    anio = ?, 
                                    categoria = ?, 
                                    descripcion = ?, 
                                    estado = ? 
                                    WHERE id = ?");
            $query->bind_param("ssssssssi", $titulo, $autor, $isbn, $editorial, $anio, $categoria, $descripcion, $estado, $id);
            
            if ($query->execute()) {
                $success = 'Libro actualizado exitosamente';
            } else {
                $error = 'Error al actualizar el libro: ' . $con->error;
            }
            
            $query->close();
        }
        
        $check->close();
    }
}

// Obtener datos del libro
$query = $con->prepare("SELECT * FROM libros WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$libro = $result->fetch_assoc();

if (!$libro) {
    $con->close();
    header('Location: index.php');
    exit;
}

$query->close();
$con->close();

include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Editar Libro</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
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
        <form method="POST" action="" class="form-modern" id="form-libro">
            <div class="form-grid">
                <div class="form-group">
                    <label for="titulo" class="form-label">
                        <i class="fas fa-book"></i> Título *
                    </label>
                    <input type="text" 
                           id="titulo" 
                           name="titulo" 
                           class="form-control" 
                           required 
                           maxlength="200"
                           value="<?php echo htmlspecialchars($libro['titulo']); ?>">
                </div>

                <div class="form-group">
                    <label for="autor" class="form-label">
                        <i class="fas fa-user-edit"></i> Autor *
                    </label>
                    <input type="text" 
                           id="autor" 
                           name="autor" 
                           class="form-control" 
                           required 
                           maxlength="150"
                           value="<?php echo htmlspecialchars($libro['autor']); ?>">
                </div>

                <div class="form-group">
                    <label for="isbn" class="form-label">
                        <i class="fas fa-barcode"></i> ISBN *
                    </label>
                    <input type="text" 
                           id="isbn" 
                           name="isbn" 
                           class="form-control" 
                           required 
                           maxlength="20"
                           value="<?php echo htmlspecialchars($libro['isbn']); ?>">
                    <small class="form-help">Código ISBN del libro (único)</small>
                </div>

                <div class="form-group">
                    <label for="editorial" class="form-label">
                        <i class="fas fa-building"></i> Editorial
                    </label>
                    <input type="text" 
                           id="editorial" 
                           name="editorial" 
                           class="form-control" 
                           maxlength="100"
                           value="<?php echo htmlspecialchars($libro['editorial'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="anio" class="form-label">
                        <i class="fas fa-calendar"></i> Año de Publicación
                    </label>
                    <input type="number" 
                           id="anio" 
                           name="anio" 
                           class="form-control" 
                           min="1000" 
                           max="<?php echo date('Y'); ?>"
                           value="<?php echo htmlspecialchars($libro['anio'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="categoria" class="form-label">
                        <i class="fas fa-tags"></i> Categoría
                    </label>
                    <select id="categoria" name="categoria" class="form-control">
                        <option value="">Seleccionar categoría</option>
                        <option value="ficcion" <?php echo $libro['categoria'] === 'ficcion' ? 'selected' : ''; ?>>Ficción</option>
                        <option value="no-ficcion" <?php echo $libro['categoria'] === 'no-ficcion' ? 'selected' : ''; ?>>No Ficción</option>
                        <option value="ciencia" <?php echo $libro['categoria'] === 'ciencia' ? 'selected' : ''; ?>>Ciencia</option>
                        <option value="historia" <?php echo $libro['categoria'] === 'historia' ? 'selected' : ''; ?>>Historia</option>
                        <option value="tecnologia" <?php echo $libro['categoria'] === 'tecnologia' ? 'selected' : ''; ?>>Tecnología</option>
                        <option value="literatura" <?php echo $libro['categoria'] === 'literatura' ? 'selected' : ''; ?>>Literatura</option>
                        <option value="arte" <?php echo $libro['categoria'] === 'arte' ? 'selected' : ''; ?>>Arte</option>
                        <option value="filosofia" <?php echo $libro['categoria'] === 'filosofia' ? 'selected' : ''; ?>>Filosofía</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado" class="form-label">
                        <i class="fas fa-info-circle"></i> Estado
                    </label>
                    <select id="estado" name="estado" class="form-control">
                        <option value="disponible" <?php echo $libro['estado'] === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                        <option value="prestado" <?php echo $libro['estado'] === 'prestado' ? 'selected' : ''; ?>>Prestado</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="descripcion" class="form-label">
                    <i class="fas fa-align-left"></i> Descripción
                </label>
                <textarea id="descripcion" 
                          name="descripcion" 
                          class="form-control" 
                          rows="4" 
                          placeholder="Descripción o sinopsis del libro..."><?php echo htmlspecialchars($libro['descripcion'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <a href="eliminar.php?id=<?php echo $libro['id']; ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('¿Está seguro de eliminar este libro?')">
                    <i class="fas fa-trash"></i> Eliminar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
