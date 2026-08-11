<?php
/**
 * Dashboard do Painel Admin
 * 
 * Página principal do painel administrativo
 * 
 * @package ClubinhoPet\Admin
 * @version 2.0.0
 */

// Carregar bootstrap
require_once __DIR__ . '/../config/bootstrap.php';

// Verificar autenticação
require_once __DIR__ . '/middleware/auth.php';

// Obter estatísticas
$stats = [
    'total_users' => 0,
    'active_subscriptions' => 0,
    'total_pets' => 0,
    'monthly_revenue' => 0
];

if ($pdo) {
    try {
        // Total de usuários
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    } catch (Exception $e) {
        // Silenciosamente falhar
    }
}

// Obter configurações
$siteConfig = [
    'name' => $configManager->get('site.name', 'Clubinho Pet Box'),
    'email' => $configManager->get('contact.email', 'contato@clubinhopetbox.com.br'),
    'phone' => $configManager->get('contact.phone', '(11) 99999-9999')
];

// Obter URLs de checkout
$checkoutUrls = $checkoutManager->getAllCheckoutUrls();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | Clubinho Pet Box</title>
    <link rel="stylesheet" href="assets/css/admin-dashboard.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background: #f3f4f6; color: #1f2937; }
        
        .admin-container { display: flex; min-height: 100vh; }
        
        .admin-sidebar { width: 250px; background: #1f2937; color: white; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-sidebar h2 { font-size: 18px; margin-bottom: 30px; }
        .admin-sidebar nav a { display: block; padding: 12px 15px; color: #d1d5db; text-decoration: none; border-radius: 5px; margin-bottom: 8px; transition: all 0.3s; }
        .admin-sidebar nav a:hover { background: #374151; color: white; }
        .admin-sidebar nav a.active { background: #6366f1; color: white; }
        
        .admin-main { margin-left: 250px; flex: 1; }
        
        .admin-header { background: white; padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .admin-header h1 { font-size: 24px; }
        .admin-user { display: flex; align-items: center; gap: 15px; }
        .admin-user a { color: #6366f1; text-decoration: none; }
        
        .admin-content { padding: 30px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 14px; color: #6b7280; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #6366f1; }
        
        .section { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section h2 { font-size: 20px; margin-bottom: 20px; }
        
        .checkout-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .checkout-item { background: #f9fafb; padding: 15px; border-radius: 8px; border-left: 4px solid #6366f1; }
        .checkout-item h3 { margin-bottom: 10px; }
        .checkout-item .url { font-size: 12px; color: #6b7280; word-break: break-all; background: white; padding: 8px; border-radius: 4px; margin-top: 8px; }
        .checkout-item .actions { margin-top: 10px; }
        .checkout-item .actions a { display: inline-block; padding: 8px 12px; background: #6366f1; color: white; text-decoration: none; border-radius: 4px; font-size: 12px; margin-right: 5px; }
        .checkout-item .actions a:hover { background: #4f46e5; }
        
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-info { background: #dbeafe; border: 1px solid #0284c7; color: #0c4a6e; }
        .alert-success { background: #dcfce7; border: 1px solid #16a34a; color: #166534; }
        .alert-warning { background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; }
        .alert-danger { background: #fee2e2; border: 1px solid #dc2626; color: #991b1b; }
        
        .btn { display: inline-block; padding: 10px 15px; background: #6366f1; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #4f46e5; }
        .btn-secondary { background: #6b7280; }
        .btn-secondary:hover { background: #4b5563; }
        
        @media (max-width: 768px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; }
            .admin-main { margin-left: 0; }
            .stats-grid { grid-template-columns: 1fr; }
            .checkout-list { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <h2>🐾 Admin</h2>
            <nav>
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="settings.php">Configurações</a>
                <a href="checkout-urls.php">URLs de Checkout</a>
                <a href="pages.php">Páginas</a>
                <a href="media.php">Mídia</a>
                <a href="users.php">Usuários</a>
                <a href="logs.php">Logs</a>
                <a href="logout.php">Sair</a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-main">
            <!-- HEADER -->
            <header class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span>👤 <?php echo htmlspecialchars($userName); ?></span>
                    <a href="logout.php">Sair</a>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="admin-content">
                <div class="alert alert-info">
                    ✅ Bem-vindo ao novo painel administrativo! Todas as configurações do site podem ser gerenciadas aqui sem editar código.
                </div>

                <!-- ESTATÍSTICAS -->
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
                        <h3>Total de Pets</h3>
                        <div class="value"><?php echo $stats['total_pets']; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Receita do Mês</h3>
                        <div class="value">R$ 0</div>
                    </div>
                </div>

                <!-- CONFIGURAÇÕES DO SITE -->
                <div class="section">
                    <h2>Configurações do Site</h2>
                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($siteConfig['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($siteConfig['email']); ?></p>
                    <p><strong>Telefone:</strong> <?php echo htmlspecialchars($siteConfig['phone']); ?></p>
                    <a href="settings.php" class="btn">Editar Configurações</a>
                </div>

                <!-- URLs DE CHECKOUT -->
                <div class="section">
                    <h2>URLs de Checkout Externo</h2>
                    <p style="margin-bottom: 20px; color: #6b7280;">Gerencie as URLs de checkout para cada plano. O site redireciona para essas URLs sem processar pagamentos internamente.</p>
                    
                    <div class="checkout-list">
                        <?php foreach ($checkoutUrls as $planId => $url): ?>
                            <?php $planInfo = $checkoutManager->getPlanInfo($planId); ?>
                            <div class="checkout-item">
                                <h3><?php echo htmlspecialchars($planInfo['name']); ?></h3>
                                <p><?php echo htmlspecialchars($planInfo['description']); ?></p>
                                <div class="url">
                                    <strong>URL:</strong><br>
                                    <?php echo htmlspecialchars($url); ?>
                                </div>
                                <div class="actions">
                                    <a href="checkout-urls.php?plan=<?php echo urlencode($planId); ?>">Editar</a>
                                    <a href="#" onclick="testCheckoutUrl('<?php echo htmlspecialchars($planId); ?>')">Testar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <a href="checkout-urls.php" class="btn" style="margin-top: 20px;">Gerenciar URLs</a>
                </div>

                <!-- AÇÕES RÁPIDAS -->
                <div class="section">
                    <h2>Ações Rápidas</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <a href="settings.php" class="btn">Editar Configurações</a>
                        <a href="pages.php" class="btn">Gerenciar Páginas</a>
                        <a href="media.php" class="btn">Gerenciar Mídia</a>
                        <a href="users.php" class="btn">Gerenciar Usuários</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function testCheckoutUrl(planId) {
            if (!confirm('Testar URL de checkout para o plano ' + planId + '?')) {
                return;
            }
            
            fetch('/admin/api/checkout.php?action=test&plan=' + encodeURIComponent(planId))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.data.accessible) {
                            alert('✅ URL acessível! (HTTP ' + data.data.http_code + ')');
                        } else {
                            alert('⚠️ URL retornou HTTP ' + data.data.http_code);
                        }
                    } else {
                        alert('❌ Erro: ' + data.message);
                    }
                })
                .catch(error => alert('❌ Erro: ' + error.message));
        }
    </script>
</body>
</html>
