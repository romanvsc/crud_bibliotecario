<?php
/**
 * API REST para gestión de préstamos
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
            obtenerPrestamo($db, $id);
        } else {
            obtenerPrestamos($db);
        }
        break;
    
    case 'POST':
        crearPrestamo($db);
        break;
    
    case 'PUT':
        // Actualizar préstamo (devolver libro o modificar)
        if (isset($_GET['accion']) && $_GET['accion'] === 'devolver') {
            devolverPrestamo($db, $id);
        } else {
            actualizarPrestamo($db, $id);
        }
        break;
    
    case 'DELETE':
        eliminarPrestamo($db, $id);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}

/**
 * Obtener todos los préstamos con información completa
 */
function obtenerPrestamos($db) {
    try {
        $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
        $usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;
        $libro_id = isset($_GET['libro_id']) ? intval($_GET['libro_id']) : null;
        
        $query = "SELECT p.*, 
                  l.titulo as libro_titulo, 
                  l.autor as libro_autor, 
                  l.isbn as libro_isbn,
                  u.nombre_completo as usuario_nombre,
                  u.email as usuario_email,
                  u.dni as usuario_dni,
                  DATEDIFF(CURDATE(), p.fecha_devolucion) as dias_retraso
                  FROM prestamos p
                  INNER JOIN libros l ON p.libro_id = l.id
                  INNER JOIN usuarios u ON p.usuario_id = u.id
                  WHERE 1=1";
        $params = [];
        
        if (!empty($estado)) {
            $query .= " AND p.estado = ?";
            $params[] = $estado;
        }
        
        if ($usuario_id) {
            $query .= " AND p.usuario_id = ?";
            $params[] = $usuario_id;
        }
        
        if ($libro_id) {
            $query .= " AND p.libro_id = ?";
            $params[] = $libro_id;
        }
        
        $query .= " ORDER BY p.fecha_prestamo DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $prestamos = $stmt->fetchAll();
        
        // Actualizar estados vencidos
        foreach ($prestamos as &$prestamo) {
            if ($prestamo['estado'] === 'activo' && $prestamo['dias_retraso'] > 0) {
                $prestamo['vencido'] = true;
            } else {
                $prestamo['vencido'] = false;
            }
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $prestamos,
            'count' => count($prestamos)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener préstamos: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener un préstamo específico por ID
 */
function obtenerPrestamo($db, $id) {
    try {
        $query = "SELECT p.*, 
                  l.titulo as libro_titulo, 
                  l.autor as libro_autor, 
                  l.isbn as libro_isbn,
                  l.editorial as libro_editorial,
                  u.nombre_completo as usuario_nombre,
                  u.email as usuario_email,
                  u.telefono as usuario_telefono,
                  u.dni as usuario_dni,
                  DATEDIFF(CURDATE(), p.fecha_devolucion) as dias_retraso
                  FROM prestamos p
                  INNER JOIN libros l ON p.libro_id = l.id
                  INNER JOIN usuarios u ON p.usuario_id = u.id
                  WHERE p.id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $prestamo = $stmt->fetch();
        
        if ($prestamo) {
            if ($prestamo['estado'] === 'activo' && $prestamo['dias_retraso'] > 0) {
                $prestamo['vencido'] = true;
            } else {
                $prestamo['vencido'] = false;
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $prestamo
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Préstamo no encontrado'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener préstamo: ' . $e->getMessage()
        ]);
    }
}

/**
 * Crear un nuevo préstamo
 */
function crearPrestamo($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validar datos requeridos
        if (empty($data['libro_id']) || empty($data['usuario_id']) || empty($data['fecha_devolucion'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Faltan datos requeridos: libro_id, usuario_id y fecha_devolucion son obligatorios'
            ]);
            return;
        }
        
        // Verificar que el libro existe y está disponible
        $check_libro = $db->prepare("SELECT estado FROM libros WHERE id = ?");
        $check_libro->execute([$data['libro_id']]);
        $libro = $check_libro->fetch();
        
        if (!$libro) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'El libro no existe'
            ]);
            return;
        }
        
        if ($libro['estado'] !== 'disponible') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'El libro no está disponible para préstamo'
            ]);
            return;
        }
        
        // Verificar que el usuario existe y está activo
        $check_usuario = $db->prepare("SELECT estado FROM usuarios WHERE id = ?");
        $check_usuario->execute([$data['usuario_id']]);
        $usuario = $check_usuario->fetch();
        
        if (!$usuario) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'El usuario no existe'
            ]);
            return;
        }
        
        if ($usuario['estado'] !== 'activo') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'El usuario no está activo'
            ]);
            return;
        }
        
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            // Crear el préstamo
            $query = "INSERT INTO prestamos (libro_id, usuario_id, fecha_prestamo, fecha_devolucion, observaciones, estado) 
                      VALUES (?, ?, CURDATE(), ?, ?, 'activo')";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                $data['libro_id'],
                $data['usuario_id'],
                $data['fecha_devolucion'],
                $data['observaciones'] ?? null
            ]);
            
            $prestamo_id = $db->lastInsertId();
            
            // Actualizar el estado del libro a 'prestado'
            $update_libro = $db->prepare("UPDATE libros SET estado = 'prestado' WHERE id = ?");
            $update_libro->execute([$data['libro_id']]);
            
            $db->commit();
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Préstamo creado exitosamente',
                'id' => $prestamo_id
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear préstamo: ' . $e->getMessage()
        ]);
    }
}

/**
 * Devolver un préstamo (marcar como devuelto)
 */
function devolverPrestamo($db, $id) {
    try {
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de préstamo requerido'
            ]);
            return;
        }
        
        // Verificar que el préstamo existe y está activo
        $check = $db->prepare("SELECT libro_id, estado FROM prestamos WHERE id = ?");
        $check->execute([$id]);
        $prestamo = $check->fetch();
        
        if (!$prestamo) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Préstamo no encontrado'
            ]);
            return;
        }
        
        if ($prestamo['estado'] !== 'activo') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'El préstamo ya fue devuelto'
            ]);
            return;
        }
        
        // Iniciar transacción
        $db->beginTransaction();
        
        try {
            // Actualizar el préstamo
            $query = "UPDATE prestamos SET 
                      estado = 'devuelto', 
                      fecha_dev_real = CURDATE()
                      WHERE id = ?";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            
            // Actualizar el estado del libro a 'disponible'
            $update_libro = $db->prepare("UPDATE libros SET estado = 'disponible' WHERE id = ?");
            $update_libro->execute([$prestamo['libro_id']]);
            
            $db->commit();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Libro devuelto exitosamente'
            ]);
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al devolver préstamo: ' . $e->getMessage()
        ]);
    }
}

/**
 * Actualizar un préstamo existente
 */
function actualizarPrestamo($db, $id) {
    try {
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de préstamo requerido'
            ]);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar si el préstamo existe
        $check = $db->prepare("SELECT id FROM prestamos WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Préstamo no encontrado'
            ]);
            return;
        }
        
        $query = "UPDATE prestamos SET 
                  fecha_devolucion = ?, 
                  observaciones = ?
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['fecha_devolucion'],
            $data['observaciones'] ?? null,
            $id
        ]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Préstamo actualizado exitosamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar préstamo: ' . $e->getMessage()
        ]);
    }
}

/**
 * Eliminar un préstamo (solo si está en estado devuelto)
 */
function eliminarPrestamo($db, $id) {
    try {
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de préstamo requerido'
            ]);
            return;
        }
        
        // Verificar estado del préstamo
        $check = $db->prepare("SELECT estado, libro_id FROM prestamos WHERE id = ?");
        $check->execute([$id]);
        $prestamo = $check->fetch();
        
        if (!$prestamo) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Préstamo no encontrado'
            ]);
            return;
        }
        
        if ($prestamo['estado'] === 'activo') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar un préstamo activo. Primero debe devolverse el libro.'
            ]);
            return;
        }
        
        $query = "DELETE FROM prestamos WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Préstamo eliminado exitosamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar préstamo: ' . $e->getMessage()
        ]);
    }
}
?>