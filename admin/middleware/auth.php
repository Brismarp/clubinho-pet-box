<?php
/**
 * Middleware de Autenticação
 * 
 * Verifica se o usuário está autenticado
 * Redireciona para login se não estiver
 * 
 * @package ClubinhoPet\Admin
 * @version 2.0.0
 */

// Garantir que a sessão foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se está logado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Redirecionar para login
    header('Location: /admin/login.php');
    exit;
}

// Verificar se a sessão expirou
$sessionTimeout = 7200; // 2 horas
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionTimeout) {
    // Sessão expirada
    session_destroy();
    header('Location: /admin/login.php?expired=1');
    exit;
}

// Atualizar tempo da última atividade
$_SESSION['last_activity'] = time();

// Obter informações do usuário
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'Admin';
$userEmail = $_SESSION['user_email'] ?? '';
