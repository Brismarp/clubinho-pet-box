<?php
/**
 * Script de Diagnóstico - Clubinho Pet Box
 * 
 * Verifica problemas comuns de instalação
 * 
 * Acesse: https://clubinhopetbox.com.br/diagnostic.php
 */

// Desabilitar output buffering para ver erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cores para output
$colors = [
    'success' => '#28a745',
    'error' => '#dc3545',
    'warning' => '#ffc107',
    'info' => '#17a2b8'
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - Clubinho Pet Box</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 30px; text-align: center; }
        .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section h2 { color: #333; margin-bottom: 15px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .check { display: flex; align-items: center; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .check-icon { font-size: 20px; margin-right: 15px; }
        .check-text { flex: 1; }
        .check-value { font-family: monospace; color: #666; font-size: 12px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; }
        .summary { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .footer { text-align: center; color: #666; margin-top: 40px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico - Clubinho Pet Box</h1>

        <!-- INFORMAÇÕES DO SERVIDOR -->
        <div class="section">
            <h2>📊 Informações do Servidor</h2>
            
            <?php
            // PHP Version
            $php_version = phpversion();
            $php_ok = version_compare($php_version, '7.4', '>=');
            ?>
            <div class="check <?php echo $php_ok ? 'success' : 'error'; ?>">
                <span class="check-icon"><?php echo $php_ok ? '✅' : '❌'; ?></span>
                <div class="check-text">
                    <strong>PHP Version</strong>
                    <div class="check-value">Versão: <?php echo $php_version; ?> (Requerido: 7.4+)</div>
                </div>
            </div>

            <?php
            // Servidor Web
            $server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido';
            ?>
            <div class="check info">
                <span class="check-icon">ℹ️</span>
                <div class="check-text">
                    <strong>Servidor Web</strong>
                    <div class="check-value"><?php echo htmlspecialchars($server_software); ?></div>
                </div>
            </div>

            <?php
            // Memória
            $memory_limit = ini_get('memory_limit');
            $memory_bytes = (int)$memory_limit * 1024 * 1024;
            $memory_ok = $memory_bytes >= 128 * 1024 * 1024;
            ?>
            <div class="check <?php echo $memory_ok ? 'success' : 'warning'; ?>">
                <span class="check-icon"><?php echo $memory_ok ? '✅' : '⚠️'; ?></span>
                <div class="check-text">
                    <strong>Limite de Memória</strong>
                    <div class="check-value"><?php echo $memory_limit; ?> (Recomendado: 128M+)</div>
                </div>
            </div>

            <?php
            // Upload máximo
            $upload_max = ini_get('upload_max_filesize');
            $post_max = ini_get('post_max_size');
            ?>
            <div class="check info">
                <span class="check-icon">ℹ️</span>
                <div class="check-text">
                    <strong>Limite de Upload</strong>
                    <div class="check-value">Upload: <?php echo $upload_max; ?> | POST: <?php echo $post_max; ?></div>
                </div>
            </div>
        </div>

        <!-- EXTENSÕES PHP -->
        <div class="section">
            <h2>🔌 Extensões PHP</h2>

            <?php
            $extensions = [
                'pdo' => 'PDO (Banco de Dados)',
                'pdo_mysql' => 'PDO MySQL',
                'json' => 'JSON',
                'curl' => 'cURL',
                'openssl' => 'OpenSSL',
                'mbstring' => 'Multibyte String',
                'fileinfo' => 'File Info',
                'gd' => 'GD (Imagens)',
            ];

            foreach ($extensions as $ext => $name) {
                $loaded = extension_loaded($ext);
                ?>
                <div class="check <?php echo $loaded ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $loaded ? '✅' : '❌'; ?></span>
                    <div class="check-text">
                        <strong><?php echo $name; ?></strong>
                        <div class="check-value"><?php echo $loaded ? 'Carregada' : 'NÃO CARREGADA'; ?></div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- ARQUIVOS E DIRETÓRIOS -->
        <div class="section">
            <h2>📁 Arquivos e Diretórios</h2>

            <?php
            $checks = [
                'config/database.php' => 'Configuração do Banco',
                'config/bootstrap.php' => 'Bootstrap',
                'lib/ConfigManager.php' => 'ConfigManager',
                'admin/login.php' => 'Admin Login',
                'admin/dashboard.php' => 'Admin Dashboard',
                'admin/api/settings.php' => 'API Settings',
                'checkout.php' => 'Checkout',
                'index.html' => 'Página Inicial',
                'database/migrations/002_complete_schema.sql' => 'SQL Schema',
            ];

            foreach ($checks as $file => $name) {
                $exists = file_exists($file);
                $readable = $exists ? is_readable($file) : false;
                ?>
                <div class="check <?php echo $exists ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $exists ? '✅' : '❌'; ?></span>
                    <div class="check-text">
                        <strong><?php echo $name; ?></strong>
                        <div class="check-value">
                            <?php 
                            if ($exists) {
                                echo "Arquivo: <code>$file</code>";
                                if (!$readable) echo " (NÃO LEGÍVEL)";
                            } else {
                                echo "Arquivo NÃO ENCONTRADO: <code>$file</code>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>

            <!-- Permissões de Diretórios -->
            <h3 style="margin-top: 20px; margin-bottom: 10px;">Permissões de Diretórios</h3>
            <?php
            $dirs = [
                'logs' => 'Logs',
                'admin' => 'Admin',
                'database' => 'Database',
            ];

            foreach ($dirs as $dir => $name) {
                $exists = is_dir($dir);
                $writable = $exists ? is_writable($dir) : false;
                $perms = $exists ? substr(sprintf('%o', fileperms($dir)), -4) : 'N/A';
                ?>
                <div class="check <?php echo $writable ? 'success' : ($exists ? 'warning' : 'error'); ?>">
                    <span class="check-icon"><?php echo $writable ? '✅' : '⚠️'; ?></span>
                    <div class="check-text">
                        <strong><?php echo $name; ?></strong>
                        <div class="check-value">
                            Permissões: <code><?php echo $perms; ?></code>
                            <?php echo $writable ? '(Escrita OK)' : '(Sem permissão de escrita)'; ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- BANCO DE DADOS -->
        <div class="section">
            <h2>🗄️ Banco de Dados</h2>

            <?php
            $db_host = 'localhost';
            $db_name = 'u463356968_clubinhopet';
            $db_user = 'u463356968_clubinhopet_us';
            $db_pass = 'Carpe1936!';

            try {
                $pdo = new PDO(
                    "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
                    $db_user,
                    $db_pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );

                $db_ok = true;
                ?>
                <div class="check success">
                    <span class="check-icon">✅</span>
                    <div class="check-text">
                        <strong>Conexão com Banco de Dados</strong>
                        <div class="check-value">Conectado com sucesso</div>
                    </div>
                </div>

                <?php
                // Verificar tabelas
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $table_count = count($tables);
                $tables_ok = $table_count >= 13;
                ?>
                <div class="check <?php echo $tables_ok ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $tables_ok ? '✅' : '❌'; ?></span>
                    <div class="check-text">
                        <strong>Tabelas do Banco</strong>
                        <div class="check-value">
                            <?php echo $table_count; ?> tabelas encontradas (Esperado: 13)
                            <?php if ($tables_ok) echo "<br>Tabelas: " . implode(", ", array_slice($tables, 0, 5)) . "..."; ?>
                        </div>
                    </div>
                </div>

                <?php
                // Verificar dados iniciais
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM plans");
                $plans_count = $stmt->fetch()['count'];
                $plans_ok = $plans_count >= 3;
                ?>
                <div class="check <?php echo $plans_ok ? 'success' : 'warning'; ?>">
                    <span class="check-icon"><?php echo $plans_ok ? '✅' : '⚠️'; ?></span>
                    <div class="check-text">
                        <strong>Planos de Assinatura</strong>
                        <div class="check-value"><?php echo $plans_count; ?> planos encontrados (Esperado: 3)</div>
                    </div>
                </div>

                <?php
                // Verificar configurações
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
                $settings_count = $stmt->fetch()['count'];
                $settings_ok = $settings_count >= 20;
                ?>
                <div class="check <?php echo $settings_ok ? 'success' : 'warning'; ?>">
                    <span class="check-icon"><?php echo $settings_ok ? '✅' : '⚠️'; ?></span>
                    <div class="check-text">
                        <strong>Configurações</strong>
                        <div class="check-value"><?php echo $settings_count; ?> configurações encontradas (Esperado: 30+)</div>
                    </div>
                </div>

                <?php
                // Verificar usuário admin
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                $admin_count = $stmt->fetch()['count'];
                $admin_ok = $admin_count >= 1;
                ?>
                <div class="check <?php echo $admin_ok ? 'success' : 'error'; ?>">
                    <span class="check-icon"><?php echo $admin_ok ? '✅' : '❌'; ?></span>
                    <div class="check-text">
                        <strong>Usuário Admin</strong>
                        <div class="check-value"><?php echo $admin_count; ?> admin(s) encontrado(s)</div>
                    </div>
                </div>

                <?php
                // Verificar URLs de checkout
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM checkout_urls");
                $urls_count = $stmt->fetch()['count'];
                $urls_ok = $urls_count >= 3;
                ?>
                <div class="check <?php echo $urls_ok ? 'success' : 'warning'; ?>">
                    <span class="check-icon"><?php echo $urls_ok ? '✅' : '⚠️'; ?></span>
                    <div class="check-text">
                        <strong>URLs de Checkout</strong>
                        <div class="check-value"><?php echo $urls_count; ?> URL(s) configurada(s) (Esperado: 3)</div>
                    </div>
                </div>

            } catch (PDOException $e) {
                $db_ok = false;
                ?>
                <div class="check error">
                    <span class="check-icon">❌</span>
                    <div class="check-text">
                        <strong>Conexão com Banco de Dados</strong>
                        <div class="check-value">Erro: <?php echo htmlspecialchars($e->getMessage()); ?></div>
                    </div>
                </div>

                <div class="check warning">
                    <span class="check-icon">⚠️</span>
                    <div class="check-text">
                        <strong>Verificação de Tabelas</strong>
                        <div class="check-value">Não foi possível verificar (banco não conectado)</div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- CONFIGURAÇÕES -->
        <div class="section">
            <h2>⚙️ Configurações</h2>

            <?php
            // Verificar arquivo de configuração
            if (file_exists('config/database.php')) {
                include 'config/database.php';
                ?>
                <div class="check info">
                    <span class="check-icon">ℹ️</span>
                    <div class="check-text">
                        <strong>Banco de Dados Configurado</strong>
                        <div class="check-value">
                            Host: <code><?php echo $db_host; ?></code><br>
                            Database: <code><?php echo $db_name; ?></code><br>
                            Usuário: <code><?php echo $db_user; ?></code>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>

            <?php
            // Verificar bootstrap
            if (file_exists('config/bootstrap.php')) {
                ?>
                <div class="check success">
                    <span class="check-icon">✅</span>
                    <div class="check-text">
                        <strong>Bootstrap Carregado</strong>
                        <div class="check-value">Arquivo config/bootstrap.php encontrado</div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <!-- RESUMO -->
        <div class="section">
            <h2>📋 Resumo</h2>
            <div class="summary">
                <p><strong>✅ Verificação Concluída</strong></p>
                <p>Se todos os itens acima estão com ✅, seu site está pronto para funcionar!</p>
                <p style="margin-top: 15px; color: #666;">
                    <strong>Próximos passos:</strong><br>
                    1. Acesse: <code>https://clubinhopetbox.com.br/admin/login.php</code><br>
                    2. Faça login com: <code>admin@clubinhopetbox.com.br</code><br>
                    3. Altere a senha do admin<br>
                    4. Configure as URLs de checkout<br>
                    5. Teste o site completo
                </p>
                <p style="margin-top: 15px; color: #dc3545;">
                    <strong>⚠️ Importante:</strong> Delete este arquivo (diagnostic.php) após verificar!
                </p>
            </div>
        </div>

        <div class="footer">
            <p>Clubinho Pet Box v2.0 - Diagnóstico</p>
            <p>Gerado em: <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
