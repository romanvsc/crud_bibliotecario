<?php
/**
 * API REST para gestión de libros
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
            obtenerLibro($db, $id);
        } else {
            obtenerLibros($db);
        }
        break;
    
    case 'POST':
        crearLibro($db);
        break;
    
    case 'PUT':
        actualizarLibro($db, $id);
        break;
    
    case 'DELETE':
        eliminarLibro($db, $id);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}

/**
 * Obtener todos los libros o filtrar por búsqueda
 */
function obtenerLibros($db) {
    try {
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
        $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
        $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
        
        $query = "SELECT * FROM libros WHERE 1=1";
        $params = [];
        
        if (!empty($busqueda)) {
            $query .= " AND (titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?)";
            $busqueda_param = "%$busqueda%";
            $params[] = $busqueda_param;
            $params[] = $busqueda_param;
            $params[] = $busqueda_param;
        }
        
        if (!empty($categoria)) {
            $query .= " AND categoria = ?";
            $params[] = $categoria;
        }
        
        if (!empty($estado)) {
            $query .= " AND estado = ?";
            $params[] = $estado;
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $libros = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $libros,
            'count' => count($libros)
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener libros: ' . $e->getMessage()
        ]);
    }
}

/**
 * Obtener un libro específico por ID
 */
function obtenerLibro($db, $id) {
    try {
        $query = "SELECT * FROM libros WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        $libro = $stmt->fetch();
        
        if ($libro) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $libro
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Libro no encontrado'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener libro: ' . $e->getMessage()
        ]);
    }
}

/**
 * Crear un nuevo libro
 */
function crearLibro($db) {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validar datos requeridos
        if (empty($data['titulo']) || empty($data['autor']) || empty($data['isbn'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Faltan datos requeridos: título, autor e ISBN son obligatorios'
            ]);
            return;
        }
        
        // Verificar si el ISBN ya existe
        $check = $db->prepare("SELECT id FROM libros WHERE isbn = ?");
        $check->execute([$data['isbn']]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Ya existe un libro con ese ISBN'
            ]);
            return;
        }
        
        $query = "INSERT INTO libros (titulo, autor, isbn, editorial, anio, categoria, descripcion, estado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['titulo'],
            $data['autor'],
            $data['isbn'],
            $data['editorial'] ?? null,
            $data['anio'] ?? null,
            $data['categoria'] ?? null,
            $data['descripcion'] ?? null,
            $data['estado'] ?? 'disponible'
        ]);
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Libro creado exitosamente',
            'id' => $db->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear libro: ' . $e->getMessage()
        ]);
    }
}

/**
 * Actualizar un libro existente
 */
function actualizarLibro($db, $id) {
    try {
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de libro requerido'
            ]);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar si el libro existe
        $check = $db->prepare("SELECT id FROM libros WHERE id = ?");
        $check->execute([$id]);
        if (!$check->fetch()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Libro no encontrado'
            ]);
            return;
        }
        
        $query = "UPDATE libros SET 
                  titulo = ?, 
                  autor = ?, 
                  isbn = ?, 
                  editorial = ?, 
                  anio = ?, 
                  categoria = ?, 
                  descripcion = ?, 
                  estado = ?
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $data['titulo'],
            $data['autor'],
            $data['isbn'],
            $data['editorial'] ?? null,
            $data['anio'] ?? null,
            $data['categoria'] ?? null,
            $data['descripcion'] ?? null,
            $data['estado'] ?? 'disponible',
            $id
        ]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Libro actualizado exitosamente'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar libro: ' . $e->getMessage()
        ]);
    }
}

/**
 * Eliminar un libro
 */
function eliminarLibro($db, $id) {
    try {
        if (!$id) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de libro requerido'
            ]);
            return;
        }
        
        // Verificar si el libro tiene préstamos activos
        $check = $db->prepare("SELECT COUNT(*) as count FROM prestamos WHERE libro_id = ? AND estado = 'activo'");
        $check->execute([$id]);
        $result = $check->fetch();
        
        if ($result['count'] > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'No se puede eliminar el libro porque tiene préstamos activos'
            ]);
            return;
        }
        
        $query = "DELETE FROM libros WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Libro eliminado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Libro no encontrado'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar libro: ' . $e->getMessage()
        ]);
    }
}
?>