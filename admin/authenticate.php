<?php
session_start();

// Credenciais do admin
$admin_username = 'AsmoD';
$admin_password = 'Carpe1936!';

// Receber dados do formulário
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validar credenciais
if ($username === $admin_username && $password === $admin_password) {
    // Login bem-sucedido
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_login_time'] = time();
    
    // Redirecionar para o dashboard
    header('Location: index.php');
    exit;
} else {
    // Login falhou
    header('Location: login.php?error=1');
    exit;
}
