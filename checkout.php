<?php
/**
 * CHECKOUT EXTERNO - REDIRECIONAMENTO STATELESS (v2.0)
 * 
 * Clubinho Pet Box
 * 
 * Redireciona para checkout externo sem processar pagamento
 * Sem estado, sem API, sem SDK - apenas redirecionamento direto
 * 
 * URLs de Checkout (Configuráveis via Admin):
 * - Happy:  https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50
 * - Love:   https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66
 * - Deluxe: https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322
 */

// ============================================
// CONFIGURAÇÕES HARDCODED (Fallback)
// ============================================

$CHECKOUT_URLS = [
    'happy' => 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50',
    'love' => 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66',
    'deluxe' => 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322',
];

// ============================================
// VALIDAÇÃO
// ============================================

// Obter plano da query string
$plan = isset($_GET['plan']) ? strtolower(trim($_GET['plan'])) : null;

// Se não houver plano, redirecionar para planos
if (!$plan || !isset($CHECKOUT_URLS[$plan])) {
    header('Location: /planos.html');
    exit;
}

// ============================================
// OBTER URL DE CHECKOUT
// ============================================

$checkout_url = $CHECKOUT_URLS[$plan];

// ============================================
// LOGGING (Opcional)
// ============================================

// Criar diretório de logs se não existir
if (!is_dir(__DIR__ . '/logs')) {
    @mkdir(__DIR__ . '/logs', 0755, true);
}

// Registrar redirecionamento
$log_entry = json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'plan' => $plan,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 100),
    'referrer' => $_SERVER['HTTP_REFERER'] ?? 'direct',
]);

@file_put_contents(
    __DIR__ . '/logs/checkout_redirects.log',
    $log_entry . PHP_EOL,
    FILE_APPEND
);

// ============================================
// REDIRECIONAR PARA CHECKOUT EXTERNO
// ============================================

header('Location: ' . $checkout_url);
exit;

// ============================================
// FALLBACK HTML (Nunca deve ser alcançado)
// ============================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecionando para Checkout...</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 400px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
        }
        p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }
        a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            padding: 10px 20px;
            border: 2px solid #667eea;
            border-radius: 5px;
            transition: all 0.3s;
        }
        a:hover {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>Redirecionando...</h1>
        <p>Você está sendo redirecionado para o checkout.</p>
        <p>Se não for redirecionado automaticamente, clique no botão abaixo:</p>
        <a href="<?php echo htmlspecialchars($checkout_url); ?>">Ir para Checkout</a>
    </div>

    <script>
        // Redirecionar após 1 segundo (fallback)
        setTimeout(function() {
            window.location.href = '<?php echo htmlspecialchars($checkout_url); ?>';
        }, 1000);
    </script>
</body>
</html>
