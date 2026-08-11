<?php
/**
 * Script de Correção Automática de Botões de Checkout
 * Corrige TODOS os botões "Assinar" para apontar para os links corretos
 * 
 * Uso: Acesse https://clubinhopetbox.com.br/fix-checkout-buttons.php
 * Após corrigir, delete este arquivo
 */

// Configuração
$checkout_urls = [
    'happy' => 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50',
    'love' => 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66',
    'deluxe' => 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322'
];

$files_to_fix = [
    'index.html',
    'planos.html',
    'sobre.html',
    'contato.html',
    'quiz.html',
    'assinatura.html'
];

$root_path = dirname(__FILE__);
$fixed_files = [];
$errors = [];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção de Botões de Checkout</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #f9fafb;
            color: #111827;
        }
        .container {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        h1 {
            color: #000;
            margin-bottom: 1rem;
        }
        .status {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .status.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .file-list {
            list-style: none;
            padding: 0;
        }
        .file-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .file-list li:last-child {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .badge.fixed {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge.error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }
        code {
            background-color: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Correção de Botões de Checkout</h1>
        
        <?php
        // Processar correções
        foreach ($files_to_fix as $file) {
            $file_path = $root_path . '/' . $file;
            
            if (!file_exists($file_path)) {
                $errors[$file] = "Arquivo não encontrado";
                continue;
            }
            
            // Ler conteúdo
            $content = file_get_contents($file_path);
            if ($content === false) {
                $errors[$file] = "Não foi possível ler o arquivo";
                continue;
            }
            
            $original_content = $content;
            
            // Substituir botões de checkout
            // Padrão 1: <a href="">Assinar Happy</a>
            $content = preg_replace(
                '/<a\s+href="[^"]*"\s*class="[^"]*"[^>]*>Assinar Happy<\/a>/i',
                '<a href="' . $checkout_urls['happy'] . '" class="btn btn-primary">Assinar Happy</a>',
                $content
            );
            
            // Padrão 2: [Assinar Happy]()
            $content = str_replace(
                '[Assinar Happy]()',
                '[Assinar Happy](' . $checkout_urls['happy'] . ')',
                $content
            );
            
            // Love
            $content = preg_replace(
                '/<a\s+href="[^"]*"\s*class="[^"]*"[^>]*>Assinar Love<\/a>/i',
                '<a href="' . $checkout_urls['love'] . '" class="btn btn-primary">Assinar Love</a>',
                $content
            );
            
            $content = str_replace(
                '[Assinar Love]()',
                '[Assinar Love](' . $checkout_urls['love'] . ')',
                $content
            );
            
            // Deluxe
            $content = preg_replace(
                '/<a\s+href="[^"]*"\s*class="[^"]*"[^>]*>Assinar Deluxe<\/a>/i',
                '<a href="' . $checkout_urls['deluxe'] . '" class="btn btn-primary">Assinar Deluxe</a>',
                $content
            );
            
            $content = str_replace(
                '[Assinar Deluxe]()',
                '[Assinar Deluxe](' . $checkout_urls['deluxe'] . ')',
                $content
            );
            
            // Verificar se houve mudanças
            if ($content !== $original_content) {
                // Salvar arquivo corrigido
                if (file_put_contents($file_path, $content) !== false) {
                    $fixed_files[$file] = "Corrigido com sucesso";
                } else {
                    $errors[$file] = "Não foi possível salvar o arquivo";
                }
            } else {
                $fixed_files[$file] = "Nenhuma alteração necessária";
            }
        }
        
        // Exibir resultados
        if (!empty($fixed_files)) {
            echo '<div class="status success">';
            echo '<strong>Arquivos Corrigidos:</strong>';
            echo '<ul class="file-list">';
            foreach ($fixed_files as $file => $status) {
                echo '<li>' . htmlspecialchars($file) . ' <span class="badge fixed">OK</span></li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
        if (!empty($errors)) {
            echo '<div class="status error">';
            echo '<strong>Erros:</strong>';
            echo '<ul class="file-list">';
            foreach ($errors as $file => $error) {
                echo '<li>' . htmlspecialchars($file) . ' <span class="badge error">' . htmlspecialchars($error) . '</span></li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
        echo '<div class="status info">';
        echo '<strong>URLs de Checkout Aplicadas:</strong>';
        echo '<ul class="file-list">';
        echo '<li><strong>Happy:</strong> <code>' . htmlspecialchars($checkout_urls['happy']) . '</code></li>';
        echo '<li><strong>Love:</strong> <code>' . htmlspecialchars($checkout_urls['love']) . '</code></li>';
        echo '<li><strong>Deluxe:</strong> <code>' . htmlspecialchars($checkout_urls['deluxe']) . '</code></li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="warning">';
        echo '<strong>Próximos Passos:</strong>';
        echo '<ol>';
        echo '<li>Limpe o cache do navegador (Ctrl+Shift+Delete)</li>';
        echo '<li>Acesse <code>https://clubinhopetbox.com.br/planos.html</code> e verifique se os botões têm links</li>';
        echo '<li>Teste clicando em um botão "Assinar"</li>';
        echo '<li>Após confirmar que tudo funciona, <strong>delete este arquivo</strong></li>';
        echo '</ol>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
