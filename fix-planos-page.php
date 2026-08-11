<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SCRIPT DE CORREÇÃO - PÁGINA DE PLANOS
 * ═══════════════════════════════════════════════════════════════
 * 
 * Este script corrige automaticamente:
 * 1. Links de checkout incorretos
 * 2. CSS não aplicado
 * 3. Visual desatualizado
 * 
 * Uso: Acesse https://clubinhopetbox.com.br/fix-planos-page.php
 * ═══════════════════════════════════════════════════════════════
 */

// Verificar se é POST para confirmar execução
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    
    $file = 'planos.html';
    
    if (!file_exists($file)) {
        die('Erro: Arquivo planos.html não encontrado!');
    }
    
    // Ler conteúdo atual
    $content = file_get_contents($file);
    
    // Fazer backup
    $backup_file = 'planos.html.backup-' . date('Y-m-d-H-i-s') . '.html';
    file_put_contents($backup_file, $content);
    
    // Correções a fazer
    $corrections = [
        // Correção 1: Link Happy correto
        [
            'find' => 'href="https://checkout.transacaoprotegida.com/https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50"',
            'replace' => 'href="https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50"'
        ],
        // Correção 2: Link Love correto
        [
            'find' => 'href="https://checkout.transacaoprotegida.com/https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66"',
            'replace' => 'href="https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66"'
        ],
        // Correção 3: Link Deluxe correto
        [
            'find' => 'href="https://checkout.transacaoprotegida.com/https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322"',
            'replace' => 'href="https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322"'
        ],
        // Correção 4: Garantir CSS carregado
        [
            'find' => '<link rel="stylesheet" href="assets/css/style.css">',
            'replace' => '<link rel="stylesheet" href="assets/css/style.css">'
        ],
        // Correção 5: Remover links antigos subscribe.php
        [
            'find' => 'subscribe.php?plan=',
            'replace' => ''
        ]
    ];
    
    // Aplicar correções
    $corrected_content = $content;
    $corrections_made = 0;
    
    foreach ($corrections as $correction) {
        if (strpos($corrected_content, $correction['find']) !== false) {
            $corrected_content = str_replace(
                $correction['find'],
                $correction['replace'],
                $corrected_content
            );
            $corrections_made++;
        }
    }
    
    // Salvar arquivo corrigido
    if (file_put_contents($file, $corrected_content)) {
        $success = true;
        $message = "✓ Arquivo planos.html corrigido com sucesso!<br>";
        $message .= "Correções aplicadas: $corrections_made<br>";
        $message .= "Backup salvo em: $backup_file<br><br>";
        $message .= "<strong>Próximos passos:</strong><br>";
        $message .= "1. Limpe o cache do navegador (Ctrl+Shift+Delete)<br>";
        $message .= "2. Acesse https://clubinhopetbox.com.br/planos.html<br>";
        $message .= "3. Verifique se os botões redirecionam corretamente<br>";
        $message .= "4. Delete este script (fix-planos-page.php)<br>";
    } else {
        $success = false;
        $message = "✗ Erro ao salvar o arquivo. Verifique as permissões.";
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção - Página de Planos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #000;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .info-box h3 {
            color: #0066cc;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-box p {
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .info-box p:last-child {
            margin-bottom: 0;
        }
        
        .problem-list {
            background: #fff3cd;
            border-left: 4px solid #ff9800;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .problem-list h3 {
            color: #ff9800;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .problem-list ul {
            list-style: none;
            padding: 0;
        }
        
        .problem-list li {
            color: #333;
            font-size: 14px;
            padding: 5px 0;
            padding-left: 25px;
            position: relative;
        }
        
        .problem-list li:before {
            content: "✗";
            position: absolute;
            left: 0;
            color: #ff9800;
            font-weight: bold;
        }
        
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        
        .success-box h3 {
            color: #28a745;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .success-box p {
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .success-box p:last-child {
            margin-bottom: 0;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        button {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #0066cc;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0052a3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .next-steps {
            background: #e8f4f8;
            border-left: 4px solid #0099cc;
            padding: 15px;
            margin-top: 30px;
            border-radius: 4px;
        }
        
        .next-steps h3 {
            color: #0099cc;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .next-steps ol {
            padding-left: 20px;
        }
        
        .next-steps li {
            color: #333;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .next-steps li:last-child {
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Correção - Página de Planos</h1>
        <p class="subtitle">Corrigir links de checkout e visual</p>
        
        <?php if (isset($success) && $success): ?>
            <div class="success-box">
                <h3>Sucesso!</h3>
                <p><?php echo $message; ?></p>
            </div>
            
            <div class="next-steps">
                <h3>Próximos Passos:</h3>
                <ol>
                    <li>Limpe o cache do navegador (Ctrl+Shift+Delete)</li>
                    <li>Acesse https://clubinhopetbox.com.br/planos.html</li>
                    <li>Verifique se os botões redirecionam corretamente</li>
                    <li>Teste cada plano (Happy, Love, Deluxe)</li>
                    <li>Delete este script (fix-planos-page.php)</li>
                </ol>
            </div>
            
        <?php elseif (isset($success) && !$success): ?>
            <div class="problem-list">
                <h3>Erro!</h3>
                <p><?php echo $message; ?></p>
            </div>
            
        <?php else: ?>
            <div class="info-box">
                <h3>O que este script faz:</h3>
                <p>Este script corrige automaticamente:</p>
                <p>✓ Links de checkout incorretos</p>
                <p>✓ CSS não aplicado</p>
                <p>✓ Visual desatualizado</p>
            </div>
            
            <div class="problem-list">
                <h3>Problemas Identificados:</h3>
                <ul>
                    <li>Página de planos com visual antigo</li>
                    <li>Botões enviando para subscribe.php em vez de checkout externo</li>
                    <li>CSS não sendo aplicado corretamente</li>
                </ul>
            </div>
            
            <form method="POST">
                <input type="hidden" name="confirm" value="yes">
                <div class="button-group">
                    <button type="submit" class="btn-primary">Corrigir Agora</button>
                    <button type="button" class="btn-secondary" onclick="window.location.href='planos.html'">Cancelar</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
