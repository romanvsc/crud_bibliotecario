<?php
/**
 * Endpoint AJAX para búsqueda de libros en tiempo real
 * Retorna resultados en formato JSON
 */

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../obtenerBaseDeDatos.php';

// Verificar que sea una petición AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$busqueda = $_GET['q'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$estado = $_GET['estado'] ?? '';

try {
    $con = ObtenerDB();
    
    $query = "SELECT * FROM libros WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($busqueda)) {
        $query .= " AND (titulo LIKE ? OR autor LIKE ? OR isbn LIKE ?)";
        $busqueda_param = "%$busqueda%";
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $params[] = $busqueda_param;
        $types .= "sss";
    }
    
    if (!empty($categoria)) {
        $query .= " AND categoria = ?";
        $params[] = $categoria;
        $types .= "s";
    }
    
    if (!empty($estado)) {
        $query .= " AND estado = ?";
        $params[] = $estado;
        $types .= "s";
    }
    
    $query .= " ORDER BY titulo ASC LIMIT 50";
    
    $stmt = $con->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $libros = $result->fetch_all(MYSQLI_ASSOC);
    
    $stmt->close();
    $con->close();
    
    echo json_encode([
        'success' => true,
        'data' => $libros,
        'count' => count($libros)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la búsqueda: ' . $e->getMessage()
    ]);
}
?>
