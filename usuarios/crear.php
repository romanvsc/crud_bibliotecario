<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../obtenerBaseDeDatos.php';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $con = ObtenerDB();
    
    $dni = trim($_POST['dni']);
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $tipo_usuario = $_POST['tipo_usuario'];
    $estado = $_POST['estado'];
    
    // Validaciones
    $errores = [];
    
    if (empty($dni)) {
        $errores[] = "El DNI es obligatorio";
    } elseif (!preg_match('/^[0-9]{8}[A-Za-z]$/', $dni)) {
        $errores[] = "El DNI debe tener 8 números y una letra";
    }
    
    if (empty($nombre_completo)) {
        $errores[] = "El nombre completo es obligatorio";
    }
    
    if (empty($email)) {
        $errores[] = "El correo electrónico es obligatorio";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio";
    } elseif (!preg_match('/^[0-9]{9}$/', $telefono)) {
        $errores[] = "El teléfono debe tener 9 dígitos";
    }
    
    // Verificar DNI único
    if (empty($errores)) {
        $stmt = $con->prepare("SELECT id FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = "Ya existe un usuario con ese DNI";
        }
        $stmt->close();
    }
    
    // Verificar email único
    if (empty($errores)) {
        $stmt = $con->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = "Ya existe un usuario con ese correo electrónico";
        }
        $stmt->close();
    }
    
    if (empty($errores)) {
        $stmt = $con->prepare("INSERT INTO usuarios (dni, nombre_completo, email, telefono, direccion, tipo_usuario, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssss", $dni, $nombre_completo, $email, $telefono, $direccion, $tipo_usuario, $estado);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Usuario creado exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: index.php");
            exit;
        } else {
            $errores[] = "Error al crear el usuario: " . $con->error;
        }
        $stmt->close();
    }
    
    $con->close();
}

$titulo_pagina = 'Crear Usuario';
include '../includes/header.php';
?>

<div class="page-container">
    <div class="page-header">
        <h1><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h1>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errores as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" class="form-card">
            <div class="form-section">
                <h2><i class="fas fa-id-card"></i> Información Personal</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="dni">DNI *</label>
                        <input type="text" id="dni" name="dni" required 
                               pattern="[0-9]{8}[A-Za-z]" 
                               placeholder="12345678A"
                               value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>">
                        <small>8 números y una letra</small>
                    </div>

                    <div class="form-group">
                        <label for="nombre_completo">Nombre Completo *</label>
                        <input type="text" id="nombre_completo" name="nombre_completo" required
                               value="<?= htmlspecialchars($_POST['nombre_completo'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico *</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono *</label>
                        <input type="tel" id="telefono" name="telefono" required 
                               pattern="[0-9]{9}"
                               placeholder="612345678"
                               value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                        <small>9 dígitos</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="3"><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2><i class="fas fa-cog"></i> Configuración</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo de Usuario *</label>
                        <select id="tipo_usuario" name="tipo_usuario" required>
                            <option value="">Seleccione...</option>
                            <option value="estudiante" <?= ($_POST['tipo_usuario'] ?? '') === 'estudiante' ? 'selected' : '' ?>>Estudiante</option>
                            <option value="profesor" <?= ($_POST['tipo_usuario'] ?? '') === 'profesor' ? 'selected' : '' ?>>Profesor</option>
                            <option value="externo" <?= ($_POST['tipo_usuario'] ?? '') === 'externo' ? 'selected' : '' ?>>Externo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado *</label>
                        <select id="estado" name="estado" required>
                            <option value="activo" <?= ($_POST['estado'] ?? 'activo') === 'activo' ? 'selected' : '' ?>>Activo</option>
                            <option value="inactivo" <?= ($_POST['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Crear Usuario
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
