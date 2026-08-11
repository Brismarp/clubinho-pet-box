<?php
/**
 * CLUBINHO PET - Configuração Principal
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

// ============================================
// CONFIGURAÇÕES GERAIS
// ============================================

define('APP_NAME', 'CLUBINHO PET');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'production'); // 'development' ou 'production'

// URLs
define('BASE_URL', 'https://clubinhopetbox.com.br');
define('ADMIN_URL', BASE_URL . '/admin');
define('API_URL', BASE_URL . '/api');

// Contato
define('CONTACT_EMAIL', 'contato@clubinhopetbox.com.br');
define('CONTACT_PHONE', '(11) 99999-9999'); // ⚠️ ALTERE ESTE TELEFONE

// ============================================
// SEGURANÇA
// ============================================

define('SESSION_NAME', 'clubinhopet_session');
define('SESSION_LIFETIME', 7200); // 2 horas em segundos
define('CSRF_TOKEN_NAME', 'csrf_token');

// Configurar sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS obrigatório
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

session_name(SESSION_NAME);
session_start();

// ============================================
// TIMEZONE
// ============================================

date_default_timezone_set('America/Sao_Paulo');

// ============================================
// ERROS
// ============================================

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================
// TIPOS DE BOXES
// ============================================

define('BOX_TYPES', [
    'happy' => [
        'name' => 'Clubinho Pet Happy',
        'description' => 'Box básica com produtos essenciais',
        'items_count' => '4 a 6 itens',
        'price_monthly' => 79.90,
        'price_quarterly' => 69.90,
        'price_yearly' => 59.90
    ],
    'love' => [
        'name' => 'Clubinho Pet Love',
        'description' => 'Box premium com produtos exclusivos',
        'items_count' => '6 a 8 itens',
        'price_monthly' => 129.90,
        'price_quarterly' => 119.90,
        'price_yearly' => 109.90
    ],
    'deluxe' => [
        'name' => 'Clubinho Pet Deluxe',
        'description' => 'Box super premium com produtos de luxo',
        'items_count' => '8 a 10 itens',
        'price_monthly' => 199.90,
        'price_quarterly' => 179.90,
        'price_yearly' => 159.90
    ]
]);

// ============================================
// PLANOS DE ASSINATURA
// ============================================

define('SUBSCRIPTION_PLANS', [
    'monthly' => 'Mensal',
    'quarterly' => 'Trimestral',
    'yearly' => 'Anual'
]);

// ============================================
// PERFIS DE PETS
// ============================================

define('PET_PROFILES', [
    'economic' => [
        'name' => 'Econômico',
        'icon' => '🟢',
        'description' => 'Produtos essenciais de ótimo custo-benefício'
    ],
    'premium' => [
        'name' => 'Premium',
        'icon' => '🔵',
        'description' => 'Produtos exclusivos e de alta qualidade'
    ],
    'natural' => [
        'name' => 'Natural',
        'icon' => '🟣',
        'description' => 'Produtos naturais e orgânicos'
    ],
    'playful' => [
        'name' => 'Brinquedão',
        'icon' => '🟠',
        'description' => 'Brinquedos interativos e estimulantes'
    ],
    'health' => [
        'name' => 'Saúde & Bem-estar',
        'icon' => '❤️',
        'description' => 'Produtos focados em saúde e cuidados'
    ]
]);

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

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
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validar token CSRF
 */
function validate_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
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
 * Formatar data e hora
 */
function format_datetime($datetime) {
    if (empty($datetime)) return '';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Validar e-mail
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
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
 * Registrar atividade
 */
function log_activity($action, $userId = null, $data = []) {
    try {
        db()->insert('activity_logs', [
            'user_id' => $userId,
            'action' => $action,
            'data' => json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        // Silenciosamente falhar se a tabela não existir
    }
}

/**
 * Registrar erro
 */
function log_error($message, $context = []) {
    $logFile = __DIR__ . '/../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
    $logMessage = "[{$timestamp}] ERROR: {$message}{$contextStr}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// ============================================
// AUTOLOAD
// ============================================

// Carregar configurações do banco de dados
require_once __DIR__ . '/database.php';

// Carregar configurações de e-mail
require_once __DIR__ . '/mail.php';

// Carregar configurações do MISTIC PAY

// Carregar models
foreach (glob(__DIR__ . '/../models/*.php') as $model) {
    require_once $model;
}
