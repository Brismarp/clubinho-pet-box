<?php
/**
 * API de Planos - v1.0
 * 
 * Endpoints:
 * GET  /api/v1/plans           - Obter todos os planos ativos
 * GET  /api/v1/plans/{id}      - Obter plano específico
 * POST /api/v1/plans           - Criar plano (requer autenticação)
 * PUT  /api/v1/plans/{id}      - Atualizar plano (requer autenticação)
 * DELETE /api/v1/plans/{id}    - Deletar plano (requer autenticação)
 * 
 * @author Clubinho Pet Box
 * @version 1.0
 */

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratamento de OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Carregar configurações
require_once __DIR__ . '/../../config/database.php';

// Conectar ao banco
try {
    $db = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Obter método e rota
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', array_filter(explode('/', $path)));

// Extrair ID se fornecido
$id = end($parts) !== 'plans' && is_numeric(end($parts)) ? end($parts) : null;

// Rotear requisição
try {
    switch ($method) {
        case 'GET':
            if ($id) {
                // GET /api/v1/plans/{id}
                handleGetSingle($db, $id);
            } else {
                // GET /api/v1/plans
                handleGetAll($db);
            }
            break;
            
        case 'POST':
            // POST /api/v1/plans
            handlePost($db);
            break;
            
        case 'PUT':
            // PUT /api/v1/plans/{id}
            handlePut($db, $id);
            break;
            
        case 'DELETE':
            // DELETE /api/v1/plans/{id}
            handleDelete($db, $id);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// ============================================================================
// FUNÇÕES DE TRATAMENTO
// ============================================================================

/**
 * Obter todos os planos ativos
 */
function handleGetAll($db) {
    $stmt = $db->query("
        SELECT * FROM plans 
        WHERE is_active = TRUE 
        ORDER BY display_order ASC
    ");
    $plans = $stmt->fetchAll();
    
    // Decodificar JSON de features
    foreach ($plans as &$plan) {
        $plan['features'] = json_decode($plan['features'], true) ?? [];
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $plans,
        'count' => count($plans),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obter plano específico
 */
function handleGetSingle($db, $id) {
    $stmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
    $stmt->execute([$id]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        http_response_code(404);
        echo json_encode(['error' => 'Plan not found']);
        return;
    }
    
    // Decodificar JSON de features
    $plan['features'] = json_decode($plan['features'], true) ?? [];
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $plan,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Criar plano
 */
function handlePost($db) {
    // Verificar autenticação
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    // Obter dados JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['name']) || !isset($input['slug']) || !isset($input['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: name, slug, price']);
        return;
    }
    
    $name = $input['name'];
    $slug = $input['slug'];
    $price = $input['price'];
    $description = $input['description'] ?? '';
    $checkout_url = $input['checkout_url'] ?? '';
    $features = json_encode($input['features'] ?? []);
    $is_active = $input['is_active'] ?? true;
    $is_popular = $input['is_popular'] ?? false;
    $display_order = $input['display_order'] ?? 0;
    
    try {
        $stmt = $db->prepare("
            INSERT INTO plans (name, slug, description, price, checkout_url, features, is_active, is_popular, display_order)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $name, $slug, $description, $price, $checkout_url, $features, 
            $is_active ? 1 : 0, $is_popular ? 1 : 0, $display_order
        ]);
        
        $id = $db->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Plan created successfully',
            'id' => $id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Atualizar plano
 */
function handlePut($db, $id) {
    // Verificar autenticação
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Plan ID is required']);
        return;
    }
    
    // Obter dados JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        return;
    }
    
    try {
        // Construir query dinâmica
        $updates = [];
        $values = [];
        
        if (isset($input['name'])) {
            $updates[] = 'name = ?';
            $values[] = $input['name'];
        }
        if (isset($input['slug'])) {
            $updates[] = 'slug = ?';
            $values[] = $input['slug'];
        }
        if (isset($input['description'])) {
            $updates[] = 'description = ?';
            $values[] = $input['description'];
        }
        if (isset($input['price'])) {
            $updates[] = 'price = ?';
            $values[] = $input['price'];
        }
        if (isset($input['checkout_url'])) {
            $updates[] = 'checkout_url = ?';
            $values[] = $input['checkout_url'];
        }
        if (isset($input['features'])) {
            $updates[] = 'features = ?';
            $values[] = json_encode($input['features']);
        }
        if (isset($input['is_active'])) {
            $updates[] = 'is_active = ?';
            $values[] = $input['is_active'] ? 1 : 0;
        }
        if (isset($input['is_popular'])) {
            $updates[] = 'is_popular = ?';
            $values[] = $input['is_popular'] ? 1 : 0;
        }
        if (isset($input['display_order'])) {
            $updates[] = 'display_order = ?';
            $values[] = $input['display_order'];
        }
        
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields to update']);
            return;
        }
        
        $values[] = $id;
        
        $query = "UPDATE plans SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute($values);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Plan updated successfully',
            'id' => $id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Deletar plano
 */
function handleDelete($db, $id) {
    // Verificar autenticação
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Plan ID is required']);
        return;
    }
    
    try {
        $stmt = $db->prepare("DELETE FROM plans WHERE id = ?");
        $stmt->execute([$id]);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Plan deleted successfully',
            'id' => $id,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

/**
 * Verificar autenticação
 */
function isAuthenticated() {
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        return false;
    }
    
    $auth = $headers['Authorization'];
    
    if (strpos($auth, 'Bearer ') !== 0) {
        return false;
    }
    
    $token = substr($auth, 7);
    
    return !empty($token);
}

?>
