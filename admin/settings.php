<?php
/**
 * Configurações Gerais do Site
 * 
 * Página para editar configurações gerais
 * 
 * @package ClubinhoPet\Admin
 * @version 2.0.0
 */

// Carregar bootstrap
require_once __DIR__ . '/../config/bootstrap.php';

// Verificar autenticação
require_once __DIR__ . '/middleware/auth.php';

// Verificar CSRF
require_once __DIR__ . '/middleware/csrf.php';

// Obter configurações atuais
$siteConfig = [
    'name' => $configManager->get('site.name', 'Clubinho Pet Box'),
    'description' => $configManager->get('site.description', ''),
    'logo' => $configManager->get('site.logo', ''),
    'favicon' => $configManager->get('site.favicon', '')
];

$brandingConfig = [
    'primary_color' => $configManager->get('branding.primary_color', '#6366f1'),
    'secondary_color' => $configManager->get('branding.secondary_color', '#ffffff'),
    'accent_color' => $configManager->get('branding.accent_color', '#fbbf24'),
    'text_color' => $configManager->get('branding.text_color', '#1f2937')
];

$contactConfig = [
    'email' => $configManager->get('contact.email', ''),
    'phone' => $configManager->get('contact.phone', ''),
    'whatsapp' => $configManager->get('contact.whatsapp', '')
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações | Admin Clubinho Pet Box</title>
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
        
        .section { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .section h2 { font-size: 20px; margin-bottom: 20px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 5px; font-family: inherit; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-group input[type="color"] { width: 60px; height: 40px; }
        
        .btn { display: inline-block; padding: 10px 15px; background: #6366f1; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
        .btn:hover { background: #4f46e5; }
        
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .alert-success { background: #dcfce7; border: 1px solid #16a34a; color: #166534; }
        .alert-danger { background: #fee2e2; border: 1px solid #dc2626; color: #991b1b; }
        
        .color-preview { display: inline-block; width: 40px; height: 40px; border-radius: 5px; border: 1px solid #d1d5db; margin-left: 10px; vertical-align: middle; }
        
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        
        @media (max-width: 768px) {
            .admin-sidebar { width: 100%; height: auto; position: relative; }
            .admin-main { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <h2>🐾 Admin</h2>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="settings.php" class="active">Configurações</a>
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
                <h1>Configurações Gerais</h1>
                <div class="admin-user">
                    <span>👤 <?php echo htmlspecialchars($userName); ?></span>
                    <a href="logout.php">Sair</a>
                </div>
            </header>

            <!-- CONTENT -->
            <div class="admin-content">
                <div id="alert-container"></div>

                <!-- CONFIGURAÇÕES DO SITE -->
                <div class="section">
                    <h2>Informações do Site</h2>
                    
                    <div class="form-group">
                        <label for="site_name">Nome do Site</label>
                        <input type="text" id="site_name" value="<?php echo htmlspecialchars($siteConfig['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="site_description">Descrição</label>
                        <textarea id="site_description"><?php echo htmlspecialchars($siteConfig['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="site_logo">URL da Logo</label>
                        <input type="url" id="site_logo" value="<?php echo htmlspecialchars($siteConfig['logo']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="site_favicon">URL do Favicon</label>
                        <input type="url" id="site_favicon" value="<?php echo htmlspecialchars($siteConfig['favicon']); ?>">
                    </div>

                    <button class="btn" onclick="saveSiteConfig()">Salvar Configurações do Site</button>
                </div>

                <!-- BRANDING -->
                <div class="section">
                    <h2>Cores e Branding</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="primary_color">Cor Primária</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="color" id="primary_color" value="<?php echo htmlspecialchars($brandingConfig['primary_color']); ?>">
                                <span class="color-preview" id="primary_preview" style="background-color: <?php echo htmlspecialchars($brandingConfig['primary_color']); ?>;"></span>
                                <input type="text" value="<?php echo htmlspecialchars($brandingConfig['primary_color']); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="secondary_color">Cor Secundária</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="color" id="secondary_color" value="<?php echo htmlspecialchars($brandingConfig['secondary_color']); ?>">
                                <span class="color-preview" id="secondary_preview" style="background-color: <?php echo htmlspecialchars($brandingConfig['secondary_color']); ?>;"></span>
                                <input type="text" value="<?php echo htmlspecialchars($brandingConfig['secondary_color']); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="accent_color">Cor de Destaque (Ouro)</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="color" id="accent_color" value="<?php echo htmlspecialchars($brandingConfig['accent_color']); ?>">
                                <span class="color-preview" id="accent_preview" style="background-color: <?php echo htmlspecialchars($brandingConfig['accent_color']); ?>;"></span>
                                <input type="text" value="<?php echo htmlspecialchars($brandingConfig['accent_color']); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="text_color">Cor do Texto</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="color" id="text_color" value="<?php echo htmlspecialchars($brandingConfig['text_color']); ?>">
                                <span class="color-preview" id="text_preview" style="background-color: <?php echo htmlspecialchars($brandingConfig['text_color']); ?>;"></span>
                                <input type="text" value="<?php echo htmlspecialchars($brandingConfig['text_color']); ?>" readonly style="flex: 1; font-family: monospace;">
                            </div>
                        </div>
                    </div>

                    <button class="btn" onclick="saveBrandingConfig()">Salvar Cores</button>
                </div>

                <!-- CONTATO -->
                <div class="section">
                    <h2>Informações de Contato</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_email">Email</label>
                            <input type="email" id="contact_email" value="<?php echo htmlspecialchars($contactConfig['email']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_phone">Telefone</label>
                            <input type="tel" id="contact_phone" value="<?php echo htmlspecialchars($contactConfig['phone']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="contact_whatsapp">WhatsApp</label>
                            <input type="tel" id="contact_whatsapp" value="<?php echo htmlspecialchars($contactConfig['whatsapp']); ?>" placeholder="5511999999999">
                        </div>
                    </div>

                    <button class="btn" onclick="saveContactConfig()">Salvar Contato</button>
                </div>
            </div>
        </main>
    </div>

    <script>
        const csrfToken = '<?php echo htmlspecialchars(generate_csrf_token()); ?>';

        // Atualizar preview de cores
        document.getElementById('primary_color').addEventListener('change', function() {
            document.getElementById('primary_preview').style.backgroundColor = this.value;
            this.parentElement.querySelector('input[type="text"]').value = this.value;
        });
        document.getElementById('secondary_color').addEventListener('change', function() {
            document.getElementById('secondary_preview').style.backgroundColor = this.value;
            this.parentElement.querySelector('input[type="text"]').value = this.value;
        });
        document.getElementById('accent_color').addEventListener('change', function() {
            document.getElementById('accent_preview').style.backgroundColor = this.value;
            this.parentElement.querySelector('input[type="text"]').value = this.value;
        });
        document.getElementById('text_color').addEventListener('change', function() {
            document.getElementById('text_preview').style.backgroundColor = this.value;
            this.parentElement.querySelector('input[type="text"]').value = this.value;
        });

        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alertClass = 'alert alert-' + type;
            const icon = type === 'success' ? '✅' : '❌';
            alertContainer.innerHTML = `<div class="${alertClass}">${icon} ${message}</div>`;
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 3000);
        }

        function saveSiteConfig() {
            const data = {
                'site.name': document.getElementById('site_name').value,
                'site.description': document.getElementById('site_description').value,
                'site.logo': document.getElementById('site_logo').value,
                'site.favicon': document.getElementById('site_favicon').value
            };

            saveBatch(data, 'Configurações do site salvas com sucesso!');
        }

        function saveBrandingConfig() {
            const data = {
                'branding.primary_color': document.getElementById('primary_color').value,
                'branding.secondary_color': document.getElementById('secondary_color').value,
                'branding.accent_color': document.getElementById('accent_color').value,
                'branding.text_color': document.getElementById('text_color').value
            };

            saveBatch(data, 'Cores salvas com sucesso!');
        }

        function saveContactConfig() {
            const data = {
                'contact.email': document.getElementById('contact_email').value,
                'contact.phone': document.getElementById('contact_phone').value,
                'contact.whatsapp': document.getElementById('contact_whatsapp').value
            };

            saveBatch(data, 'Informações de contato salvas com sucesso!');
        }

        function saveBatch(data, successMessage) {
            const promises = [];

            for (const [key, value] of Object.entries(data)) {
                promises.push(
                    fetch('/admin/api/settings.php?action=save', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ key, value })
                    })
                    .then(response => response.json())
                );
            }

            Promise.all(promises)
                .then(results => {
                    const allSuccess = results.every(r => r.success);
                    if (allSuccess) {
                        showAlert(successMessage, 'success');
                    } else {
                        showAlert('Erro ao salvar algumas configurações', 'danger');
                    }
                })
                .catch(error => {
                    showAlert('Erro: ' + error.message, 'danger');
                });
        }
    </script>
</body>
</html>
