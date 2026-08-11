<?php
/**
 * API de Configurações - v1.0
 * 
 * Endpoints:
 * GET  /api/v1/config           - Obter todas as configurações públicas
 * GET  /api/v1/config/{key}     - Obter configuração específica
 * POST /api/v1/config           - Criar/atualizar configuração (requer autenticação)
 * DELETE /api/v1/config/{key}   - Deletar configuração (requer autenticação)
 * 
 * @author Clubinho Pet Box
 * @version 1.0
 */

// Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratamento de OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Carregar configurações
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../lib/ConfigManager_v2.php';

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

// Inicializar ConfigManager
$configManager = new ConfigManager($db);

// Obter método e rota
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', array_filter(explode('/', $path)));

// Extrair chave se fornecida
$key = end($parts) !== 'config' ? end($parts) : null;

// Rotear requisição
try {
    switch ($method) {
        case 'GET':
            if ($key) {
                // GET /api/v1/config/{key}
                handleGetSingle($configManager, $key);
            } else {
                // GET /api/v1/config
                handleGetAll($configManager);
            }
            break;
            
        case 'POST':
            // POST /api/v1/config
            handlePost($configManager, $db);
            break;
            
        case 'DELETE':
            // DELETE /api/v1/config/{key}
            handleDelete($configManager, $key);
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
 * Obter todas as configurações públicas
 */
function handleGetAll($configManager) {
    $configs = $configManager->getAll();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $configs,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Obter configuração específica
 */
function handleGetSingle($configManager, $key) {
    $value = $configManager->get($key);
    
    if ($value === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Configuration not found']);
        return;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'key' => $key,
        'value' => $value,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Criar/atualizar configuração
 */
function handlePost($configManager, $db) {
    // Verificar autenticação
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    // Obter dados JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['key']) || !isset($input['value'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: key, value']);
        return;
    }
    
    $key = $input['key'];
    $value = $input['value'];
    $type = $input['type'] ?? 'string';
    $description = $input['description'] ?? '';
    $isPublic = $input['is_public'] ?? false;
    
    // Validar tipo
    $validTypes = ['string', 'integer', 'boolean', 'json', 'array'];
    if (!in_array($type, $validTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type. Must be one of: ' . implode(', ', $validTypes)]);
        return;
    }
    
    // Salvar configuração
    $success = $configManager->set($key, $value, $type, $description, $isPublic);
    
    if (!$success) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save configuration']);
        return;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Configuration saved successfully',
        'key' => $key,
        'value' => $value,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Deletar configuração
 */
function handleDelete($configManager, $key) {
    // Verificar autenticação
    if (!isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    if (!$key) {
        http_response_code(400);
        echo json_encode(['error' => 'Configuration key is required']);
        return;
    }
    
    // Deletar configuração
    $success = $configManager->delete($key);
    
    if (!$success) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete configuration']);
        return;
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Configuration deleted successfully',
        'key' => $key,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Verificar autenticação
 */
function isAuthenticated() {
    // Verificar token de autenticação
    $headers = getallheaders();
    
    if (!isset($headers['Authorization'])) {
        return false;
    }
    
    $auth = $headers['Authorization'];
    
    // Verificar formato "Bearer {token}"
    if (strpos($auth, 'Bearer ') !== 0) {
        return false;
    }
    
    $token = substr($auth, 7);
    
    // Aqui você implementaria a validação do token
    // Por enquanto, apenas verificamos se existe
    return !empty($token);
}

?>
