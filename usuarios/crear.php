<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/check_role.php';

// Solo admin y bibliotecarios pueden crear usuarios
verificarRol(['admin', 'bibliotecario']);

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
    $usuario_login = trim($_POST['usuario_login']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'] ?? 'usuario'; // Por defecto es usuario común
    
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
    
    if (empty($usuario_login)) {
        $errores[] = "El nombre de usuario es obligatorio";
    } elseif (strlen($usuario_login) < 4) {
        $errores[] = "El nombre de usuario debe tener al menos 4 caracteres";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario_login)) {
        $errores[] = "El nombre de usuario solo puede contener letras, números y guiones bajos";
    }
    
    if (empty($password)) {
        $errores[] = "La contraseña es obligatoria";
    } elseif (strlen($password) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
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
    
    // Verificar email único en usuarios
    if (empty($errores)) {
        $stmt = $con->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = "Ya existe un usuario con ese correo electrónico";
        }
        $stmt->close();
    }
    
    // Verificar usuario_login único en usuarios_sistema
    if (empty($errores)) {
        $stmt = $con->prepare("SELECT id FROM usuarios_sistema WHERE usuario = ?");
        $stmt->bind_param("s", $usuario_login);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = "Ya existe un usuario del sistema con ese nombre de usuario";
        }
        $stmt->close();
    }
    
    // Verificar email único en usuarios_sistema
    if (empty($errores)) {
        $stmt = $con->prepare("SELECT id FROM usuarios_sistema WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errores[] = "Ya existe un usuario del sistema con ese correo electrónico";
        }
        $stmt->close();
    }
    
    if (empty($errores)) {
        // Iniciar transacción para asegurar que ambas inserciones se completen
        $con->begin_transaction();
        
        try {
            // 1. Insertar en tabla usuarios (lectores/socios)
            $stmt = $con->prepare("INSERT INTO usuarios (dni, nombre_completo, email, telefono, direccion, tipo_usuario, estado, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssss", $dni, $nombre_completo, $email, $telefono, $direccion, $tipo_usuario, $estado);
            $stmt->execute();
            $usuario_id = $con->insert_id;
            $stmt->close();
            
            // 2. Insertar en tabla usuarios_sistema (acceso al sistema)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_sistema = $con->prepare("INSERT INTO usuarios_sistema (usuario, password, nombre, email, rol, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_sistema->bind_param("sssssi", $usuario_login, $password_hash, $nombre_completo, $email, $rol, $usuario_id);
            $stmt_sistema->execute();
            $stmt_sistema->close();
            
            // Confirmar transacción
            $con->commit();
            
            $_SESSION['mensaje'] = "Usuario creado exitosamente en ambos sistemas";
            $_SESSION['tipo_mensaje'] = "success";
            header("Location: index.php");
            exit;
            
        } catch (Exception $e) {
            // Revertir cambios si hay error
            $con->rollback();
            $errores[] = "Error al crear el usuario: " . $e->getMessage();
        }
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

            <div class="form-section">
                <h2><i class="fas fa-lock"></i> Acceso al Sistema *</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="usuario_login">Nombre de Usuario *</label>
                        <input type="text" id="usuario_login" name="usuario_login" required 
                               pattern="[a-zA-Z0-9_]{4,}" 
                               placeholder="usuario123"
                               value="<?= htmlspecialchars($_POST['usuario_login'] ?? '') ?>">
                        <small>Mínimo 4 caracteres (letras, números y guiones bajos)</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input type="password" id="password" name="password" required minlength="6"
                               placeholder="Mínimo 6 caracteres">
                        <small>Esta contraseña será usada para iniciar sesión</small>
                    </div>

                    <div class="form-group">
                        <label for="rol">Rol en el Sistema *</label>
                        <select id="rol" name="rol" required>
                            <option value="usuario" <?= ($_POST['rol'] ?? 'usuario') === 'usuario' ? 'selected' : '' ?>>Usuario (Solo préstamos)</option>
                            <option value="bibliotecario" <?= ($_POST['rol'] ?? '') === 'bibliotecario' ? 'selected' : '' ?>>Bibliotecario (Gestión completa)</option>
                            <option value="admin" <?= ($_POST['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador (Acceso total)</option>
                        </select>
                        <small>Define los permisos del usuario en el sistema</small>
                    </div>
                </div>
                <div class="info-box" style="margin-top: 15px; padding: 12px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px;">
                    <strong>ℹ️ Roles del sistema:</strong>
                    <ul style="margin: 8px 0 0 20px; font-size: 0.9em;">
                        <li><strong>Usuario:</strong> Solo puede solicitar préstamos y ver su historial</li>
                        <li><strong>Bibliotecario:</strong> Puede gestionar libros, usuarios y préstamos</li>
                        <li><strong>Administrador:</strong> Acceso completo al sistema</li>
                    </ul>
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
