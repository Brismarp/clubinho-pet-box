<?php
session_start();
require_once 'check_auth.php';
require_once '../config/database.php';

// Conectar ao banco de dados
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'cancel' && isset($_POST['subscription_id'])) {
        try {
            $subscriptionId = $_POST['subscription_id'];
            
            // Buscar assinatura
            $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ?");
            $stmt->execute([$subscriptionId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($subscription && $subscription['gateway_subscription_id']) {
                // MISTIC PAY não gerencia assinaturas recorrentes automaticamente
                // Cancelamento é feito apenas localmente
                
                // Atualizar status local
                $stmt = $pdo->prepare("
                    UPDATE subscriptions 
                    SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$subscriptionId]);
                
                $message = 'Assinatura cancelada com sucesso!';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = 'Erro ao cancelar assinatura: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    if ($action === 'sync' && isset($_POST['subscription_id'])) {
        try {
            $subscriptionId = $_POST['subscription_id'];
            
            // Buscar assinatura
            $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE id = ?");
            $stmt->execute([$subscriptionId]);
            $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($subscription) {
                // MISTIC PAY: Verificar últimos pagamentos para determinar status
                $stmt = $pdo->prepare("
                    SELECT status, created_at 
                    FROM payments 
                    WHERE subscription_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ");
                $stmt->execute([$subscriptionId]);
                $lastPayment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($lastPayment) {
                    $newStatus = $lastPayment['status'] === 'paid' ? 'active' : 'pending';
                    
                    $stmt = $pdo->prepare("
                        UPDATE subscriptions 
                        SET status = ?, updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$newStatus, $subscriptionId]);
                    
                    $message = 'Status atualizado com base no último pagamento!';
                    $messageType = 'success';
                } else {
                    $message = 'Nenhum pagamento encontrado para esta assinatura.';
                    $messageType = 'warning';
                }
            }
        } catch (Exception $e) {
            $message = 'Erro ao sincronizar: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Filtros
$status_filter = $_GET['status'] ?? '';
$plan_filter = $_GET['plan'] ?? '';
$search = $_GET['search'] ?? '';

// Construir query
$where = [];
$params = [];

if ($status_filter) {
    $where[] = "s.status = ?";
    $params[] = $status_filter;
}

if ($plan_filter) {
    $where[] = "s.plan = ?";
    $params[] = $plan_filter;
}

if ($search) {
    $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Buscar assinaturas
$stmt = $pdo->prepare("
    SELECT s.*, u.name as user_name, u.email as user_email, p.name as pet_name
    FROM subscriptions s 
    JOIN users u ON s.user_id = u.id 
    LEFT JOIN pets p ON s.pet_id = p.id
    {$whereClause}
    ORDER BY s.created_at DESC
    LIMIT 100
");
$stmt->execute($params);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_subscriptions,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
        SUM(CASE WHEN status = 'active' THEN price ELSE 0 END) as monthly_revenue,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
    FROM subscriptions
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinaturas | Clubinho Pet Box Admin</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="admin-style.css">
    <style>
        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-size: 12px;
            font-weight: 500;
            color: #666;
        }
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-filter {
            padding: 8px 16px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-filter:hover {
            background: #4f46e5;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-mini {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #6366f1;
        }
        .stat-mini h4 {
            font-size: 12px;
            color: #666;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .stat-mini p {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
            color: #1a1a1a;
        }
        .subscription-actions {
            display: flex;
            gap: 5px;
        }
        .btn-action {
            padding: 4px 8px;
            font-size: 11px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #f3f4f6;
            color: #374151;
        }
        .btn-action:hover {
            background: #e5e7eb;
        }
        .btn-action.danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .btn-action.danger:hover {
            background: #fecaca;
        }
        .message {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .badge-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-suspended, .badge-past_due {
            background: #fed7aa;
            color: #9a3412;
        }
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
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>Clubinho Pet Box</h1>
            <p>Admin Panel</p>
        </div>
        
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item">
                <span>📊</span> Dashboard
            </a>
            <a href="users.php" class="nav-item">
                <span>👥</span> Usuários
            </a>
            <a href="subscriptions.php" class="nav-item active">
                <span>📦</span> Assinaturas
            </a>
            <a href="payments.php" class="nav-item">
                <span>💳</span> Pagamentos
            </a>
            <a href="pets.php" class="nav-item">
                <span>🐾</span> Pets
            </a>
            <a href="settings.php" class="nav-item">
                <span>⚙️</span> Configurações
            </a>
            <a href="security.php" class="nav-item">
                <span>🔒</span> Segurança
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="admin-info">
                <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
                <small>Administrador</small>
            </div>
            <a href="logout.php" class="btn-logout">Sair</a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        
        <!-- Header -->
        <header class="content-header">
            <h2>Assinaturas</h2>
            <div class="header-actions">
                <span class="timestamp">Atualizado em: <?php echo date('d/m/Y H:i'); ?></span>
            </div>
        </header>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-mini">
                <h4>Total de Assinaturas</h4>
                <p><?php echo number_format($stats['total_subscriptions'], 0, ',', '.'); ?></p>
            </div>
            <div class="stat-mini">
                <h4>Assinaturas Ativas</h4>
                <p><?php echo number_format($stats['active_count'], 0, ',', '.'); ?></p>
            </div>
            <div class="stat-mini">
                <h4>Receita Mensal Recorrente</h4>
                <p>R$ <?php echo number_format($stats['monthly_revenue'], 2, ',', '.'); ?></p>
            </div>
            <div class="stat-mini">
                <h4>Canceladas</h4>
                <p><?php echo number_format($stats['cancelled_count'], 0, ',', '.'); ?></p>
            </div>
        </div>
        
        <!-- Filters -->
        <form method="GET" class="filters-bar">
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" name="search" placeholder="Nome ou email" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">Todos</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Ativa</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelada</option>
                    <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Suspensa</option>
                    <option value="past_due" <?php echo $status_filter === 'past_due' ? 'selected' : ''; ?>>Atrasada</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Plano</label>
                <select name="plan">
                    <option value="">Todos</option>
                    <option value="monthly" <?php echo $plan_filter === 'monthly' ? 'selected' : ''; ?>>Mensal</option>
                    <option value="quarterly" <?php echo $plan_filter === 'quarterly' ? 'selected' : ''; ?>>Trimestral</option>
                    <option value="semiannual" <?php echo $plan_filter === 'semiannual' ? 'selected' : ''; ?>>Semestral</option>
                    <option value="yearly" <?php echo $plan_filter === 'yearly' ? 'selected' : ''; ?>>Anual</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">Filtrar</button>
            <?php if ($status_filter || $plan_filter || $search): ?>
                <a href="subscriptions.php" class="btn-filter" style="background: #6b7280; text-decoration: none;">Limpar</a>
            <?php endif; ?>
        </form>
        
        <!-- Subscriptions Table -->
        <div class="card">
            <div class="card-header">
                <h3>Lista de Assinaturas</h3>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Pet</th>
                            <th>Box</th>
                            <th>Plano</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Próxima Cobrança</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscriptions)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: #999;">
                                    Nenhuma assinatura encontrada
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td>#<?php echo $sub['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($sub['user_name']); ?></strong><br>
                                        <small style="color: #666;"><?php echo htmlspecialchars($sub['user_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($sub['pet_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($sub['box_type'] ?? 'N/A')); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($sub['plan'] ?? 'N/A')); ?></td>
                                    <td>R$ <?php echo number_format($sub['price'], 2, ',', '.'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $sub['status']; ?>">
                                            <?php echo ucfirst($sub['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($sub['next_billing_date']) {
                                            echo date('d/m/Y', strtotime($sub['next_billing_date']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="subscription-actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="sync">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <button type="submit" class="btn-action" title="Sincronizar status">🔄</button>
                                            </form>
                                            
                                            <?php if ($sub['status'] === 'active'): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja cancelar esta assinatura?');">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                    <button type="submit" class="btn-action danger" title="Cancelar assinatura">❌</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </main>
    
</body>
</html>
