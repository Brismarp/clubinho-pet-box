<?php
/**
 * Middleware de Proteção CSRF
 * 
 * Valida tokens CSRF em requisições POST
 * 
 * @package ClubinhoPet\Admin
 * @version 2.0.0
 */

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
 * Obter token CSRF
 */
function get_csrf_token() {
    return generate_csrf_token();
}

/**
 * Validar token CSRF
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verificar CSRF em requisição POST
 */
function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        
        if (!$token || !validate_csrf_token($token)) {
            http_response_code(403);
            json_response([
                'success' => false,
                'message' => 'Token CSRF inválido'
            ], 403);
        }
    }
}

/**
 * Renderizar campo CSRF em formulário
 */
function csrf_field() {
    $token = get_csrf_token();
    return sprintf(
        '<input type="hidden" name="csrf_token" value="%s">',
        htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
    );
}
