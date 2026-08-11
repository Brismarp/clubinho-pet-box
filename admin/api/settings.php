<?php
/**
 * API de Configurações
 * 
 * Endpoints para gerenciar configurações do site
 * 
 * @package ClubinhoPet\Admin\API
 * @version 2.0.0
 */

// Carregar bootstrap
require_once __DIR__ . '/../../config/bootstrap.php';

// Verificar autenticação
require_once __DIR__ . '/../middleware/auth.php';

// Verificar CSRF
require_once __DIR__ . '/../middleware/csrf.php';

// Definir header JSON
header('Content-Type: application/json; charset=utf-8');

// Obter método da requisição
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'get':
            // GET /admin/api/settings.php?action=get&key=site.name
            $key = $_GET['key'] ?? null;
            
            if (!$key) {
                json_response([
                    'success' => false,
                    'message' => 'Chave não fornecida'
                ], 400);
            }
            
            $value = $configManager->get($key);
            
            json_response([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'value' => $value
                ]
            ]);
            break;
            
        case 'get_all':
            // GET /admin/api/settings.php?action=get_all
            $settings = $configManager->getAll();
            
            json_response([
                'success' => true,
                'data' => $settings
            ]);
            break;
            
        case 'save':
            // POST /admin/api/settings.php?action=save
            check_csrf();
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['key']) || !isset($data['value'])) {
                json_response([
                    'success' => false,
                    'message' => 'Chave e valor são obrigatórios'
                ], 400);
            }
            
            $key = $data['key'];
            $value = $data['value'];
            $description = $data['description'] ?? '';
            
            // Validar chave
            if (!preg_match('/^[a-z0-9_.]+$/i', $key)) {
                json_response([
                    'success' => false,
                    'message' => 'Chave inválida'
                ], 400);
            }
            
            $result = $configManager->set($key, $value, $description);
            
            if ($result) {
                json_response([
                    'success' => true,
                    'message' => 'Configuração salva com sucesso',
                    'data' => [
                        'key' => $key,
                        'value' => $value
                    ]
                ]);
            } else {
                json_response([
                    'success' => false,
                    'message' => 'Erro ao salvar configuração'
                ], 500);
            }
            break;
            
        case 'delete':
            // POST /admin/api/settings.php?action=delete
            check_csrf();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $key = $data['key'] ?? null;
            
            if (!$key) {
                json_response([
                    'success' => false,
                    'message' => 'Chave não fornecida'
                ], 400);
            }
            
            $result = $configManager->delete($key);
            
            if ($result) {
                json_response([
                    'success' => true,
                    'message' => 'Configuração deletada com sucesso'
                ]);
            } else {
                json_response([
                    'success' => false,
                    'message' => 'Erro ao deletar configuração'
                ], 500);
            }
            break;
            
        case 'history':
            // GET /admin/api/settings.php?action=history&key=site.name
            $key = $_GET['key'] ?? null;
            $limit = intval($_GET['limit'] ?? 10);
            
            if (!$key) {
                json_response([
                    'success' => false,
                    'message' => 'Chave não fornecida'
                ], 400);
            }
            
            $history = $configManager->getHistory($key, $limit);
            
            json_response([
                'success' => true,
                'data' => $history
            ]);
            break;
            
        case 'restore':
            // POST /admin/api/settings.php?action=restore
            check_csrf();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $key = $data['key'] ?? null;
            $historyId = intval($data['history_id'] ?? 0);
            
            if (!$key || $historyId <= 0) {
                json_response([
                    'success' => false,
                    'message' => 'Chave e ID do histórico são obrigatórios'
                ], 400);
            }
            
            $result = $configManager->restoreVersion($key, $historyId);
            
            if ($result) {
                json_response([
                    'success' => true,
                    'message' => 'Versão restaurada com sucesso'
                ]);
            } else {
                json_response([
                    'success' => false,
                    'message' => 'Erro ao restaurar versão'
                ], 500);
            }
            break;
            
        default:
            json_response([
                'success' => false,
                'message' => 'Ação não reconhecida'
            ], 400);
    }
    
} catch (Exception $e) {
    log_error("API Settings Error: " . $e->getMessage(), [
        'action' => $action,
        'method' => $method
    ]);
    
    json_response([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ], 500);
}
