<?php
/**
 * API REST para gestión de usuarios (lectores/socios)
 * Endpoints: GET, POST, PUT, DELETE
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . '/../config/database.php';

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Obtener ID si existe en la URL
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Instanciar base de datos
$database = new Database();
$db = $database->getConnection();

// Procesar según método HTTP
switch ($method) {
    case 'GET':
        if ($id) {
            obtenerUsuario($db, $id);
        } else {
            obtenerUsuarios($db);
        }
        break;
    
    case 'POST':
        crearUsuario($db);
        break;
    
    case 'PUT':
        actualizarUsuario($db, $id);
        break;
    
    case 'DELETE':
        eliminarUsuario($db, $id);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}

/**
 * Obtener todos los usuarios o filtrar por búsqueda
 */
function obtenerUsuarios($db) {
    try {
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
        $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
        
        $query = "SELECT u.*, 
                  COUNT(CASE WHEN p.estado = 'activo' THEN 1 END) as prestamos_activos
                  FROM usuarios u
                  LEFT JOIN prestamos p ON u.id = p.usuario_id
                  WHERE 1=1";
        $params = [];
        
        if (!empty($busqueda)) {
            $query .= " AND (u.nombre_completo LIKE ? OR u.email LIKE ? OR u.dni LIKE ?)";
            $busqueda_param = "%$busqueda%";
            $params[] = $busqueda_param;
            $params[] = $busqueda_param;
            $params[] = $busqueda_param;
        }
        
        if (!empty($estado)) {
            $query .= " AND u.estado = ?";
            $params[] = $estado;
        }
        
        $query .= " GROUP BY u.id ORDER BY u.created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $usuarios,
            'count' => count($usuarios)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener usuarios: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener un usuario específico por ID
 */
function obtenerUsuario($db, $id) {
    try {
        $query = "SELECT u.*, 
                  COUNT(CASE WHEN p.estado = 'activo' THEN 1 END) as prestamos_activos,
                  COUNT(p.id) as total_prestamos
                  FROM usuarios u
                  LEFT JOIN prestamos p ON u.id = p.usuario_id
                  WHERE u.id = ?
                  GROUP BY u.id";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            // Obtener historial de préstamos
            $query_prestamos = "SELECT p.*, l.titulo as libro_titulo, l.autor as libro_autor
                                FROM prestamos p
                                INNER JOIN libros l ON p.libro_id = l.id
                                WHERE p.usuario_id = ?
                                ORDER BY p.fecha_prestamo DESC
                                LIMIT 10";
            $stmt_prestamos = $db->prepare($query_prestamos);
            $stmt_prestamos->execute([$id]);
            $usuario['historial_prestamos'] = $stmt_prestamos->fetchAll();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $usuario
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener usuario: ' . $e->getMessage()
        ]);
    }
}

/**
 * Crear un nuevo usuario
 */
function crearUsuario($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validar datos requeridos
        if (empty($data['nombre_completo']) || empty($data['email']) || empty($data['dni'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Faltan datos requeridos: nombre completo, email y DNI son obligatorios'
            ]);
            return;
        }
        
        // Validar formato de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Formato de email inválido'
            ]);
            return;
        }
        
        // Verificar si el email ya existe
        $check = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un usuario con ese email'
            ]);
            return;
        }
        
        // Verificar si el DNI ya existe
        $check = $db->prepare("SELECT id FROM usuarios WHERE dni = ?");
        $check->execute([$data['dni']]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un usuario con ese DNI'
            ]);
            return;
        }
        
        $query = "INSERT INTO usuarios (nombre_completo, email, telefono, direccion, dni, estado) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['nombre_completo'],
            $data['email'],
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['dni'],
            $data['estado'] ?? 'activo'
        ]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'id' => $db->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear usuario: ' . $e->getMessage()
        ]);
    }
}

/**
 * Actualizar un usuario existente
 */
function actualizarUsuario($db, $id) {
    try {
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario requerido'
            ]);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar si el usuario existe
        $check = $db->prepare("SELECT id FROM usuarios WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
            return;
        }
        
        // Validar formato de email si se proporciona
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Formato de email inválido'
            ]);
            return;
        }
        
        $query = "UPDATE usuarios SET 
                  nombre_completo = ?, 
                  email = ?, 
                  telefono = ?, 
                  direccion = ?, 
                  dni = ?, 
                  estado = ?
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['nombre_completo'],
            $data['email'],
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['dni'],
            $data['estado'] ?? 'activo',
            $id
        ]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar usuario: ' . $e->getMessage()
        ]);
    }
}

/**
 * Eliminar un usuario
 */
function eliminarUsuario($db, $id) {
    try {
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de usuario requerido'
            ]);
            return;
        }
        
        // Verificar si el usuario tiene préstamos activos
        $check = $db->prepare("SELECT COUNT(*) as count FROM prestamos WHERE usuario_id = ? AND estado = 'activo'");
        $check->execute([$id]);
        $result = $check->fetch();
        
        if ($result['count'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar el usuario porque tiene préstamos activos'
            ]);
            return;
        }
        
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar usuario: ' . $e->getMessage()
        ]);
    }
}
?>