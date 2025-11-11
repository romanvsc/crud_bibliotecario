<?php
/**
 * Endpoint AJAX para validar disponibilidad de libros y usuarios
 * Retorna información en formato JSON
 */

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../obtenerBaseDeDatos.php';

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? '';

try {
    $con = ObtenerDB();
    
    switch ($accion) {
        case 'validar_libro':
            $libro_id = $_GET['libro_id'] ?? null;
            
            if (!$libro_id) {
                throw new Exception('ID de libro requerido');
            }
            
            $query = $con->prepare("SELECT id, titulo, autor, isbn, estado FROM libros WHERE id = ?");
            $query->bind_param("i", $libro_id);
            $query->execute();
            $result = $query->get_result();
            $libro = $result->fetch_assoc();
            
            if (!$libro) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Libro no encontrado'
                ]);
            } elseif ($libro['estado'] !== 'disponible') {
                echo json_encode([
                    'success' => false,
                    'message' => 'El libro no está disponible para préstamo',
                    'data' => $libro
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Libro disponible',
                    'data' => $libro
                ]);
            }
            
            $query->close();
            break;
            
        case 'validar_usuario':
            $usuario_id = $_GET['usuario_id'] ?? null;
            
            if (!$usuario_id) {
                throw new Exception('ID de usuario requerido');
            }
            
            $query = $con->prepare("SELECT u.id, u.nombre_completo, u.dni, u.email, u.estado,
                                    COUNT(CASE WHEN p.estado = 'activo' THEN 1 END) as prestamos_activos
                                    FROM usuarios u
                                    LEFT JOIN prestamos p ON u.id = p.usuario_id
                                    WHERE u.id = ?
                                    GROUP BY u.id");
            $query->bind_param("i", $usuario_id);
            $query->execute();
            $result = $query->get_result();
            $usuario = $result->fetch_assoc();
            
            if (!$usuario) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ]);
            } elseif ($usuario['estado'] !== 'activo') {
                echo json_encode([
                    'success' => false,
                    'message' => 'El usuario no está activo',
                    'data' => $usuario
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario válido',
                    'data' => $usuario
                ]);
            }
            
            $query->close();
            break;
            
        case 'verificar_disponibilidad':
            // Obtener libros disponibles
            $libros = $con->query("SELECT COUNT(*) as count FROM libros WHERE estado = 'disponible'");
            $count_libros = $libros->fetch_assoc()['count'];
            
            // Obtener usuarios activos
            $usuarios = $con->query("SELECT COUNT(*) as count FROM usuarios WHERE estado = 'activo'");
            $count_usuarios = $usuarios->fetch_assoc()['count'];
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'libros_disponibles' => $count_libros,
                    'usuarios_activos' => $count_usuarios
                ]
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
    $con->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
