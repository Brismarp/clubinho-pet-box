<?php
/**
 * Bootstrap - Inicialização da Aplicação
 * 
 * Carrega todas as dependências necessárias
 * 
 * @package ClubinhoPet
 * @version 2.0.0
 */

// ============================================
// CONFIGURAÇÕES BÁSICAS
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

date_default_timezone_set('America/Sao_Paulo');

// ============================================
// CONSTANTES
// ============================================

define('APP_NAME', 'CLUBINHO PET BOX');
define('APP_VERSION', '2.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('BASE_PATH', dirname(dirname(__FILE__)));
define('BASE_URL', 'https://clubinhopetbox.com.br');
define('ADMIN_URL', BASE_URL . '/admin');

// ============================================
// AUTOLOAD DE CLASSES
// ============================================

spl_autoload_register(function ($class) {
    // Remover namespace se existir
    $class = str_replace('ClubinhoPet\\', '', $class);
    
    $paths = [
        BASE_PATH . '/lib/',
        BASE_PATH . '/models/',
        BASE_PATH . '/controllers/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ============================================
// CARREGAR CLASSES PRINCIPAIS
// ============================================

require_once BASE_PATH . '/lib/ConfigManager.php';
require_once BASE_PATH . '/lib/CheckoutManager.php';

// ============================================
// INICIALIZAR DATABASE
// ============================================

try {
    require_once BASE_PATH . '/config/database.php';
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    error_log("Bootstrap: Erro ao conectar ao banco - " . $e->getMessage());
    $pdo = null;
}

// ============================================
// INICIALIZAR CONFIG MANAGER
// ============================================

$configManager = ConfigManager::getInstance($pdo);

// ============================================
// INICIALIZAR CHECKOUT MANAGER
// ============================================

$checkoutManager = CheckoutManager::getInstance($configManager);

// ============================================
// SESSÃO
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    
    session_name('clubinhopet_session');
    session_start();
}

// ============================================
// FUNÇÕES GLOBAIS
// ============================================

/**
 * Obter configuração
 */
function config($key, $default = null) {
    global $configManager;
    return $configManager->get($key, $default);
}

/**
 * Obter URL de checkout
 */
function checkout_url($planId) {
    global $checkoutManager;
    return $checkoutManager->getCheckoutUrl($planId);
}

/**
 * Gerar botão de checkout
 */
function checkout_button($planId, $text = 'Assinar Agora', $class = '') {
    global $checkoutManager;
    return $checkoutManager->generateCheckoutButton($planId, $text, $class);
}

/**
 * Verificar se está logado
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Verificar se é admin
 */
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Obter usuário atual
 */
function get_current_user() {
    if (!is_logged_in()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? ''
    ];
}

/**
 * Redirecionar
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Gerar token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validar token CSRF
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formatar preço
 */
function format_price($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

/**
 * Formatar data
 */
function format_date($date) {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Sanitizar string
 */
function sanitize_string($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}

/**
 * Resposta JSON
 */
function json_response($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Registrar erro
 */
function log_error($message, $context = []) {
    $logFile = BASE_PATH . '/logs/error.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[{$timestamp}] ERROR: {$message}{$contextStr}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
