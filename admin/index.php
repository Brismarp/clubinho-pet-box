<?php
session_start();
require_once 'check_auth.php';

// Tentar conectar ao banco com tratamento de erro
$db_connected = false;
$pdo = null;
$stats = [
    'total_users' => 0,
    'active_subscriptions' => 0,
    'total_pets' => 0,
    'monthly_revenue' => 0
];

try {
    require_once '../config/database.php';
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $db_connected = true;
    
    // Buscar estatísticas apenas se conectado
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $stats['total_users'] = 'N/A';
    }
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM subscriptions WHERE status = 'active'");
        $stats['active_subscriptions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $stats['active_subscriptions'] = 'N/A';
    }
    
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pets");
        $stats['total_pets'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $stats['total_pets'] = 'N/A';
    }
    
    try {
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'approved' AND MONTH(created_at) = MONTH(CURRENT_DATE())");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['monthly_revenue'] = $result['total'] ?? 0;
    } catch (Exception $e) {
        $stats['monthly_revenue'] = 'N/A';
    }
    
} catch (Exception $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Clubinho Pet Box</title>
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; }
        .alert-success { background: #d4edda; border: 1px solid #28a745; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #dc3545; color: #721c24; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .stat-card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; text-transform: uppercase; }
        .stat-card .value { font-size: 36px; font-weight: bold; color: #6366f1; }
        .quick-links { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 30px 0; }
        .quick-link { background: #6366f1; color: white; padding: 20px; border-radius: 10px; text-align: center; text-decoration: none; transition: all 0.3s; }
        .quick-link:hover { background: #4f46e5; transform: translateY(-2px); }
    </style>
    <!-- CSS Inline Mobile Fix -->
    <style>
    html, body { overflow-x: hidden !important; max-width: 100vw !important; }
    * { max-width: 100% !important; }
    img { max-width: 100% !important; height: auto !important; width: auto !important; display: block !important; }
    @media (max-width: 768px) {
        .container { max-width: 100% !important; padding: 20px !important; }
        table { display: block !important; overflow-x: auto !important; }
        .btn { width: 100% !important; display: block !important; margin-bottom: 10px !important; }
        h1 { font-size: 28px !important; }
        h2 { font-size: 24px !important; }
    }
    </style>
<style>header,.header{height:70px!important;max-height:70px!important;min-height:auto!important;padding:.75rem 0!important;display:flex!important;align-items:center!important}header img,.header img,.logo,img[alt*="logo"],img[alt*="Logo"],img[src*="logo"]{max-height:48px!important;height:auto!important;width:auto!important;max-width:200px!important}header .container,.header .container{display:flex!important;align-items:center!important;justify-content:space-between!important;height:100%!important}@media (max-width:768px){header,.header{height:50px!important;padding:.5rem 0!important}header img,.header img,.logo{max-height:40px!important;max-width:150px!important}}@media (max-width:480px){header,.header{height:45px!important}header img,.header img,.logo{max-height:35px!important;max-width:120px!important}}</style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>🐾 Clubinho Pet Box - Admin</h1>
            <div class="admin-user">
                <span>👤 <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="logout.php" class="btn-logout">Sair</a>
            </div>
        </header>

        <nav class="admin-nav">
            <a href="index.php" class="active">Dashboard</a>
            <a href="payments.php">Pagamentos</a>
            <a href="subscriptions.php">Assinaturas</a>
            <a href="settings.php">Configurações</a>
        </nav>

        <main class="admin-content">
            <h2>Dashboard</h2>

            <?php if (!$db_connected): ?>
                <div class="alert alert-danger">
                    <strong>⚠️ Erro de Conexão com Banco de Dados</strong>
                    <p>Não foi possível conectar ao banco de dados. As estatísticas não estão disponíveis.</p>
                    <details>
                        <summary>Ver detalhes do erro</summary>
                        <pre><?php echo htmlspecialchars($db_error ?? 'Erro desconhecido'); ?></pre>
                    </details>
                    <p><strong>Solução:</strong></p>
                    <ul>
                        <li>Verifique as credenciais em <code>config/database.php</code></li>
                        <li>Acesse <a href="test_conexao_banco.php">test_conexao_banco.php</a> para diagnóstico</li>
                        <li>Certifique-se de que o banco foi importado corretamente</li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <strong>✅ Sistema Operacional</strong>
                    <p>Conexão com banco de dados estabelecida com sucesso!</p>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total de Usuários</h3>
                    <div class="value"><?php echo $stats['total_users']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Assinaturas Ativas</h3>
                    <div class="value"><?php echo $stats['active_subscriptions']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pets Cadastrados</h3>
                    <div class="value"><?php echo $stats['total_pets']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Receita Mensal</h3>
                    <div class="value">
                        <?php 
                        if (is_numeric($stats['monthly_revenue'])) {
                            echo 'R$ ' . number_format($stats['monthly_revenue'], 2, ',', '.');
                        } else {
                            echo $stats['monthly_revenue'];
                        }
                        ?>
                    </div>
                </div>
            </div>

            <h3>Acesso Rápido</h3>
            <div class="quick-links">
                <a href="payments.php" class="quick-link">
                    <h4>💳 Pagamentos</h4>
                    <p>Gerenciar pagamentos</p>
                </a>
                <a href="subscriptions.php" class="quick-link">
                    <h4>📦 Assinaturas</h4>
                    <p>Gerenciar assinaturas</p>
                </a>
                <a href="settings.php" class="quick-link">
                    <h4>⚙️ Configurações</h4>
                    <p>Configurar Amplo Pay</p>
                </a>
                <a href="test_diagnostico.php" class="quick-link">
                    <h4>🔍 Diagnóstico</h4>
                    <p>Verificar sistema</p>
                </a>
            </div>

            <div class="alert alert-warning">
                <strong>🔐 Segurança</strong>
                <p>Lembre-se de deletar os arquivos de teste após a instalação:</p>
                <ul>
                    <li><code>admin/test_diagnostico.php</code></li>
                    <li><code>admin/test_conexao_banco.php</code></li>
                    <li><code>admin/gerar_hash_senha.php</code></li>
                </ul>
            </div>

            <hr style="margin: 30px 0;">

            <h3>Informações do Sistema</h3>
            <table style="width: 100%; background: white; padding: 20px; border-radius: 10px;">
                <tr>
                    <td><strong>Versão:</strong></td>
                    <td>2.0.1 FINAL</td>
                </tr>
                <tr>
                    <td><strong>Gateway de Pagamento:</strong></td>
                    <td>Amplo Pay</td>
                </tr>
                <tr>
                    <td><strong>PHP Version:</strong></td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td><strong>Servidor:</strong></td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></td>
                </tr>
                <tr>
                    <td><strong>Banco de Dados:</strong></td>
                    <td><?php echo $db_connected ? '✅ Conectado' : '❌ Desconectado'; ?></td>
                </tr>
            </table>
        </main>
    </div>
</body>
</html>
