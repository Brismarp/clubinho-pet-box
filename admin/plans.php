<?php
/**
 * Painel Admin - Gerenciamento de Planos v2.0
 * 
 * Permite:
 * - Listar planos
 * - Criar plano
 * - Editar plano
 * - Deletar plano
 * - Ativar/desativar plano
 * 
 * @author Clubinho Pet Box
 * @version 2.0
 */

session_start();

// Verificar autenticação
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Carregar configurações
require_once __DIR__ . '/../config/database.php';

// Conectar ao banco
try {
    $db = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Processar ações
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create' || $action === 'update') {
            $id = $_POST['id'] ?? null;
            $name = $_POST['name'] ?? '';
            $slug = $_POST['slug'] ?? '';
            $description = $_POST['description'] ?? '';
            $price = $_POST['price'] ?? 0;
            $checkout_url = $_POST['checkout_url'] ?? '';
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $is_popular = isset($_POST['is_popular']) ? 1 : 0;
            $display_order = $_POST['display_order'] ?? 0;
            
            if ($action === 'create') {
                $stmt = $db->prepare("
                    INSERT INTO plans (name, slug, description, price, checkout_url, is_active, is_popular, display_order)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $description, $price, $checkout_url, $is_active, $is_popular, $display_order]);
                $message = 'Plano criado com sucesso!';
            } else {
                $stmt = $db->prepare("
                    UPDATE plans SET name = ?, slug = ?, description = ?, price = ?, checkout_url = ?, is_active = ?, is_popular = ?, display_order = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $price, $checkout_url, $is_active, $is_popular, $display_order, $id]);
                $message = 'Plano atualizado com sucesso!';
            }
            $message_type = 'success';
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? null;
            $stmt = $db->prepare("DELETE FROM plans WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Plano deletado com sucesso!';
            $message_type = 'success';
        }
    } catch (Exception $e) {
        $message = 'Erro: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Obter planos
$plans = [];
try {
    $stmt = $db->query("SELECT * FROM plans ORDER BY display_order ASC");
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Plans error: ' . $e->getMessage());
}

// Obter plano para edição
$edit_plan = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM plans WHERE id = ?");
    $stmt->execute([$id]);
    $edit_plan = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos | Clubinho Pet Box Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px; color: #374151; background-color: #f3f4f6; }
        
        .admin-container { display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 250px; background-color: #1f2937; color: #f3f4f6; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar h2 { font-size: 18px; margin-bottom: 30px; }
        .sidebar nav a { display: block; padding: 12px 16px; color: #d1d5db; text-decoration: none; border-radius: 6px; margin-bottom: 8px; transition: all 200ms; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: #374151; color: #ffffff; }
        
        /* Main */
        .main { margin-left: 250px; flex: 1; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .header h1 { font-size: 32px; }
        
        /* Form */
        .form-container { background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 30px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-family: inherit; font-size: 14px; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #000000; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .checkbox-group { display: flex; gap: 20px; }
        .checkbox-group label { display: flex; align-items: center; margin-bottom: 0; }
        .checkbox-group input { width: auto; margin-right: 8px; }
        
        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; font-size: 14px; font-weight: 600; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; transition: all 200ms; }
        .btn-primary { background-color: #000000; color: #ffffff; }
        .btn-primary:hover { background-color: #1f2937; }
        .btn-secondary { background-color: #e5e7eb; color: #000000; }
        .btn-secondary:hover { background-color: #d1d5db; }
        .btn-danger { background-color: #ef4444; color: #ffffff; }
        .btn-danger:hover { background-color: #dc2626; }
        
        /* Table */
        .table-container { background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #f9fafb; padding: 12px 16px; text-align: left; font-weight: 600; border-bottom: 1px solid #e5e7eb; font-size: 12px; color: #6b7280; text-transform: uppercase; }
        td { padding: 12px 16px; border-bottom: 1px solid #e5e7eb; }
        tr:hover { background-color: #f9fafb; }
        
        /* Badge */
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .badge-active { background-color: #d1fae5; color: #065f46; }
        .badge-inactive { background-color: #fee2e2; color: #991b1b; }
        
        /* Message */
        .message { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
        .message.success { background-color: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .message.error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main { margin-left: 0; }
            .header { flex-direction: column; align-items: flex-start; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <h2>Clubinho Admin</h2>
            <nav>
                <a href="dashboard_v2.php">📊 Dashboard</a>
                <a href="settings_v2.php">⚙️ Configurações</a>
                <a href="plans_v2.php" class="active">📦 Planos</a>
                <a href="subscriptions_v2.php">👥 Assinaturas</a>
                <a href="content_v2.php">📝 Conteúdo</a>
                <a href="users_v2.php">👤 Usuários</a>
            </nav>
        </aside>
        
        <!-- Main -->
        <main class="main">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>Planos</h1>
                    <p>Gerenciar planos de assinatura</p>
                </div>
                <a href="?new=1" class="btn btn-primary">+ Novo Plano</a>
            </div>
            
            <!-- Message -->
            <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <!-- Form -->
            <?php if (isset($_GET['new']) || isset($_GET['edit'])): ?>
            <form method="POST" class="form-container">
                <h2 style="margin-bottom: 20px; font-size: 20px;">
                    <?php echo isset($_GET['edit']) ? 'Editar Plano' : 'Novo Plano'; ?>
                </h2>
                
                <input type="hidden" name="action" value="<?php echo isset($_GET['edit']) ? 'update' : 'create'; ?>">
                <?php if (isset($_GET['edit'])): ?>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nome do Plano</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_plan['name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug</label>
                        <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($edit_plan['slug'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Descrição</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($edit_plan['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Preço (R$)</label>
                        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($edit_plan['price'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="display_order">Ordem de Exibição</label>
                        <input type="number" id="display_order" name="display_order" value="<?php echo htmlspecialchars($edit_plan['display_order'] ?? '0'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="checkout_url">URL de Checkout</label>
                    <input type="url" id="checkout_url" name="checkout_url" value="<?php echo htmlspecialchars($edit_plan['checkout_url'] ?? ''); ?>" required>
                </div>
                
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="is_active" <?php echo (isset($edit_plan) && $edit_plan['is_active']) ? 'checked' : ''; ?>>
                        Ativo
                    </label>
                    <label>
                        <input type="checkbox" name="is_popular" <?php echo (isset($edit_plan) && $edit_plan['is_popular']) ? 'checked' : ''; ?>>
                        Popular
                    </label>
                </div>
                
                <div style="margin-top: 20px; display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Salvar Plano</button>
                    <a href="plans_v2.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
            <?php endif; ?>
            
            <!-- Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($plan['name']); ?></td>
                            <td>R$ <?php echo number_format($plan['price'], 2, ',', '.'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $plan['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $plan['is_active'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $plan['id']; ?>" class="btn btn-secondary" style="font-size: 12px;">Editar</a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $plan['id']; ?>">
                                    <button type="submit" class="btn btn-danger" style="font-size: 12px;" onclick="return confirm('Tem certeza?');">Deletar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
