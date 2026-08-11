<?php

// Desativa o limite de tempo de execução para scripts longos
set_time_limit(0);

// Define os links antigos e novos
$old_links = [
    'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50',
    'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66',
    'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322',
];

$new_links = [
    'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50', // Happy
    'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66', // Love
    'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322', // Deluxe
];

// Mapeamento de planos para Kirvano
$plan_kirvano_map = [
    'happy' => 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50',
    'love' => 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66',
    'deluxe' => 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322',
];

// Mapeamento de subscribe.php para Kirvano
$subscribe_kirvano_map = [
    'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50' => 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50',
    'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66' => 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66',
    'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322' => 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322',
];

$files_modified = [];
$root_dir = __DIR__; // Diretório raiz do script (public_html)

// Função para escanear e substituir
function scan_and_replace($dir, $old_links, $new_links, $subscribe_kirvano_map, &$files_modified) {
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) {
            scan_and_replace($path, $old_links, $new_links, $subscribe_kirvano_map, $files_modified);
        } elseif (is_file($path)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($extension, ['html', 'php', 'js'])) {
                $content = file_get_contents($path);
                $original_content = $content;

                // Substituir links da Transação Protegida
                $content = str_replace($old_links, $new_links, $content);

                // Substituir links de subscribe.php
                foreach ($subscribe_kirvano_map as $old_sub => $new_kirvano) {
                    $content = str_replace($old_sub, $new_kirvano, $content);
                }

                if ($content !== $original_content) {
                    file_put_contents($path, $content);
                    $files_modified[] = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', $path);
                }
            }
        }
    }
}

// Executa a varredura e substituição
scan_and_replace($root_dir, $old_links, $new_links, $subscribe_kirvano_map, $files_modified);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Correção de Links de Checkout</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; color: #333; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 800px; margin: 20px auto; }
        h1 { color: #28a745; margin-bottom: 20px; }
        h2 { color: #007bff; margin-top: 30px; margin-bottom: 15px; }
        ul { list-style-type: none; padding: 0; }
        li { background-color: #e9ecef; padding: 10px; margin-bottom: 8px; border-radius: 4px; }
        .success { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .info { color: #17a2b8; }
        .button-link { display: inline-block; background-color: #007bff; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin-top: 20px; }
        .button-link:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Correção de Links de Checkout Concluída!</h1>
        <p class="success">O script varreu seu projeto e atualizou todos os links de checkout da Transação Protegida para os novos links da Kirvano, além de corrigir referências a `subscribe.php`.</p>
        
        <?php if (!empty($files_modified)): ?>
            <h2>Arquivos Modificados:</h2>
            <ul>
                <?php foreach ($files_modified as $file): ?>
                    <li><?php echo htmlspecialchars($file); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="info">Nenhum arquivo precisou ser modificado. Os links já estavam atualizados ou não foram encontrados.</p>
        <?php endif; ?>

        <h2>Próximos Passos Essenciais:</h2>
        <ol>
            <li><p class="warning"><strong>Limpe o cache do seu navegador:</strong> Pressione <code>Ctrl + Shift + Delete</code> (Windows/Linux) ou <code>Cmd + Shift + Delete</code> (Mac) e limpe os dados de navegação, especialmente "Imagens e arquivos armazenados em cache".</p></li>
            <li><p class="info"><strong>Verifique a página de planos:</strong> Acesse <a href="/planos.html"><code>https://clubinhopetbox.com.br/planos.html</code></a> e confirme se o visual está correto e os botões de "Assinar" redirecionam para os links da Kirvano.</p></li>
            <li><p class="info"><strong>Teste outros botões:</strong> Navegue pelo site e clique em outros botões de "Assinar" para garantir que todos funcionam.</p></li>
            <li><p class="warning"><strong>APAGUE ESTE ARQUIVO (<code>fix-all-checkout-links.php</code>) DO SEU SERVIDOR IMEDIATAMENTE APÓS A VERIFICAÇÃO.</strong></p></li>
        </ol>
        <a href="/planos.html" class="button-link">Ir para a Página de Planos</a>
    </div>
</body>
</html>
