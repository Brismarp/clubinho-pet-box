<?php
/**
 * API de Checkout
 * 
 * Endpoints para gerenciar URLs de checkout externo
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

// Obter ação
$action = $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'list':
            // GET /admin/api/checkout.php?action=list
            $urls = $checkoutManager->getAllCheckoutUrls();
            
            $data = [];
            foreach ($urls as $planId => $url) {
                $planInfo = $checkoutManager->getPlanInfo($planId);
                $data[] = [
                    'plan_id' => $planId,
                    'plan_name' => $planInfo['name'] ?? $planId,
                    'checkout_url' => $url
                ];
            }
            
            json_response([
                'success' => true,
                'data' => $data
            ]);
            break;
            
        case 'get':
            // GET /admin/api/checkout.php?action=get&plan=happy
            $plan = $_GET['plan'] ?? null;
            
            if (!$plan) {
                json_response([
                    'success' => false,
                    'message' => 'Plano não fornecido'
                ], 400);
            }
            
            $url = $checkoutManager->getCheckoutUrl($plan);
            $planInfo = $checkoutManager->getPlanInfo($plan);
            
            if (!$url) {
                json_response([
                    'success' => false,
                    'message' => 'Plano não encontrado'
                ], 404);
            }
            
            json_response([
                'success' => true,
                'data' => [
                    'plan_id' => $plan,
                    'plan_info' => $planInfo,
                    'checkout_url' => $url
                ]
            ]);
            break;
            
        case 'update':
            // POST /admin/api/checkout.php?action=update
            check_csrf();
            
            $data = json_decode(file_get_contents('php://input'), true);
            $plan = $data['plan'] ?? null;
            $url = $data['url'] ?? null;
            
            if (!$plan || !$url) {
                json_response([
                    'success' => false,
                    'message' => 'Plano e URL são obrigatórios'
                ], 400);
            }
            
            // Validar URL
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                json_response([
                    'success' => false,
                    'message' => 'URL inválida'
                ], 400);
            }
            
            $result = $checkoutManager->setCheckoutUrl($plan, $url);
            
            if ($result) {
                json_response([
                    'success' => true,
                    'message' => 'URL de checkout atualizada com sucesso',
                    'data' => [
                        'plan' => $plan,
                        'url' => $url
                    ]
                ]);
            } else {
                json_response([
                    'success' => false,
                    'message' => 'Erro ao atualizar URL de checkout'
                ], 500);
            }
            break;
            
        case 'test':
            // GET /admin/api/checkout.php?action=test&plan=happy
            $plan = $_GET['plan'] ?? null;
            
            if (!$plan) {
                json_response([
                    'success' => false,
                    'message' => 'Plano não fornecido'
                ], 400);
            }
            
            if (!$checkoutManager->isValidPlan($plan)) {
                json_response([
                    'success' => false,
                    'message' => 'Plano inválido'
                ], 400);
            }
            
            $url = $checkoutManager->getCheckoutUrl($plan);
            
            // Testar se URL é acessível
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $isAccessible = $httpCode >= 200 && $httpCode < 400;
            
            json_response([
                'success' => true,
                'data' => [
                    'plan' => $plan,
                    'url' => $url,
                    'accessible' => $isAccessible,
                    'http_code' => $httpCode
                ]
            ]);
            break;
            
        default:
            json_response([
                'success' => false,
                'message' => 'Ação não reconhecida'
            ], 400);
    }
    
} catch (Exception $e) {
    log_error("API Checkout Error: " . $e->getMessage(), [
        'action' => $action
    ]);
    
    json_response([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ], 500);
}
