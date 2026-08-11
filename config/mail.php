<?php
/**
 * CLUBINHO PET - Configuração de E-mail
 * SMTP Hostinger
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

// ============================================
// CONFIGURAÇÕES SMTP
// ============================================

define('MAIL_HOST', 'smtp.hostinger.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'contato@clubinhopetbox.com.br');
define('MAIL_PASSWORD', 'SUA_SENHA_EMAIL_AQUI'); // ⚠️ ALTERE ESTA SENHA
define('MAIL_ENCRYPTION', 'tls'); // tls ou ssl
define('MAIL_FROM_EMAIL', 'contato@clubinhopetbox.com.br');
define('MAIL_FROM_NAME', 'CLUBINHO PET');

// ============================================
// CLASSE MAILER
// ============================================

class Mailer {
    
    /**
     * Enviar e-mail
     */
    public static function send($to, $subject, $body, $isHtml = true) {
        try {
            $headers = [
                'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_EMAIL . '>',
                'Reply-To: ' . MAIL_FROM_EMAIL,
                'X-Mailer: PHP/' . phpversion(),
                'MIME-Version: 1.0'
            ];
            
            if ($isHtml) {
                $headers[] = 'Content-Type: text/html; charset=UTF-8';
            } else {
                $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            }
            
            // Usar SMTP da Hostinger
            $result = self::sendSMTP($to, $subject, $body, $headers);
            
            if ($result) {
                log_activity('email_sent', null, [
                    'to' => $to,
                    'subject' => $subject
                ]);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            log_error('Email sending failed: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject
            ]);
            return false;
        }
    }
    
    /**
     * Enviar via SMTP
     */
    private static function sendSMTP($to, $subject, $body, $headers) {
        // Conectar ao servidor SMTP
        $smtp = fsockopen(MAIL_HOST, MAIL_PORT, $errno, $errstr, 30);
        
        if (!$smtp) {
            log_error('SMTP connection failed: ' . $errstr);
            return false;
        }
        
        // Ler resposta inicial
        fgets($smtp, 515);
        
        // EHLO
        fputs($smtp, "EHLO " . MAIL_HOST . "\r\n");
        fgets($smtp, 515);
        
        // STARTTLS
        if (MAIL_ENCRYPTION === 'tls') {
            fputs($smtp, "STARTTLS\r\n");
            fgets($smtp, 515);
            stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            fputs($smtp, "EHLO " . MAIL_HOST . "\r\n");
            fgets($smtp, 515);
        }
        
        // AUTH LOGIN
        fputs($smtp, "AUTH LOGIN\r\n");
        fgets($smtp, 515);
        fputs($smtp, base64_encode(MAIL_USERNAME) . "\r\n");
        fgets($smtp, 515);
        fputs($smtp, base64_encode(MAIL_PASSWORD) . "\r\n");
        $response = fgets($smtp, 515);
        
        if (strpos($response, '235') === false) {
            log_error('SMTP authentication failed');
            fclose($smtp);
            return false;
        }
        
        // MAIL FROM
        fputs($smtp, "MAIL FROM: <" . MAIL_FROM_EMAIL . ">\r\n");
        fgets($smtp, 515);
        
        // RCPT TO
        fputs($smtp, "RCPT TO: <{$to}>\r\n");
        fgets($smtp, 515);
        
        // DATA
        fputs($smtp, "DATA\r\n");
        fgets($smtp, 515);
        
        // Cabeçalhos e corpo
        $message = implode("\r\n", $headers) . "\r\n";
        $message .= "Subject: {$subject}\r\n\r\n";
        $message .= $body . "\r\n.\r\n";
        
        fputs($smtp, $message);
        fgets($smtp, 515);
        
        // QUIT
        fputs($smtp, "QUIT\r\n");
        fclose($smtp);
        
        return true;
    }
    
    /**
     * Template de e-mail base
     */
    private static function getTemplate($content) {
        return '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #6DD5FA 0%, #2980B9 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; }
                .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 12px 30px; background: #FFD93D; color: #333; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .logo { font-size: 24px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🐾 CLUBINHO PET</div>
                </div>
                <div class="content">
                    ' . $content . '
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' CLUBINHO PET - Todos os direitos reservados</p>
                    <p>clubinhopetbox.com.br | contato@clubinhopetbox.com.br</p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
    
    /**
     * E-mail de boas-vindas
     */
    public static function sendWelcome($user) {
        $content = '
            <h2>Bem-vindo ao CLUBINHO PET! 🎉</h2>
            <p>Olá <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
            <p>Estamos muito felizes em ter você e seu pet conosco!</p>
            <p>Agora você pode cadastrar seus pets e fazer o quiz para descobrir o perfil perfeito e receber boxes personalizadas todo mês.</p>
            <p style="text-align: center;">
                <a href="https://clubinhopetbox.com.br/dashboard" class="button">Acessar Minha Conta</a>
            </p>
            <p>Se tiver alguma dúvida, estamos à disposição!</p>
            <p>Equipe CLUBINHO PET 🐾</p>
        ';
        
        return self::send(
            $user['email'],
            'Bem-vindo ao CLUBINHO PET! 🐾',
            self::getTemplate($content)
        );
    }
    
    /**
     * E-mail de resultado do quiz
     */
    public static function sendQuizResult($user, $profile) {
        $content = '
            <h2>Resultado do Quiz! 🎯</h2>
            <p>Olá <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
            <p>Identificamos o perfil do seu pet:</p>
            <div style="background: #f0f8ff; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <h3 style="color: #2980B9; margin: 0;">Perfil: ' . $profile['name'] . '</h3>
                <p style="margin: 10px 0 0 0;">' . $profile['description'] . '</p>
            </div>
            <p>Box recomendada: <strong>' . $profile['box_name'] . '</strong></p>
            <p style="text-align: center;">
                <a href="https://clubinhopetbox.com.br/checkout?box=' . $profile['box_type'] . '" class="button">Assinar Agora</a>
            </p>
            <p>Equipe CLUBINHO PET 🐾</p>
        ';
        
        return self::send(
            $user['email'],
            'Resultado do Quiz - CLUBINHO PET 🎯',
            self::getTemplate($content)
        );
    }
    
    /**
     * E-mail de confirmação de assinatura
     */
    public static function sendSubscriptionConfirmation($user, $subscription) {
        $content = '
            <h2>Assinatura Confirmada! ✅</h2>
            <p>Olá <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
            <p>Sua assinatura foi confirmada com sucesso!</p>
            <div style="background: #f0f8ff; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <p><strong>Box:</strong> ' . $subscription['box_name'] . '</p>
                <p><strong>Plano:</strong> ' . $subscription['plan'] . '</p>
                <p><strong>Valor:</strong> ' . $subscription['price'] . '</p>
                <p><strong>Próxima entrega:</strong> ' . $subscription['next_delivery'] . '</p>
            </div>
            <p>Prepare-se para receber muita alegria e diversão para seu pet! 🎁</p>
            <p style="text-align: center;">
                <a href="https://clubinhopetbox.com.br/dashboard" class="button">Acompanhar Pedido</a>
            </p>
            <p>Equipe CLUBINHO PET 🐾</p>
        ';
        
        return self::send(
            $user['email'],
            'Assinatura Confirmada - CLUBINHO PET ✅',
            self::getTemplate($content)
        );
    }
    
    /**
     * E-mail de confirmação de pagamento
     */
    public static function sendPaymentConfirmation($user, $payment) {
        $content = '
            <h2>Pagamento Confirmado! 💳</h2>
            <p>Olá <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
            <p>Recebemos seu pagamento com sucesso!</p>
            <div style="background: #f0f8ff; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <p><strong>Valor:</strong> ' . $payment['amount'] . '</p>
                <p><strong>Data:</strong> ' . $payment['date'] . '</p>
            </div>
            <p>Sua box já está sendo preparada com muito carinho! 📦</p>
            <p>Equipe CLUBINHO PET 🐾</p>
        ';
        
        return self::send(
            $user['email'],
            'Pagamento Confirmado - CLUBINHO PET 💳',
            self::getTemplate($content)
        );
    }
    
    /**
     * E-mail de recuperação de senha
     */
    public static function sendPasswordReset($user, $token) {
        $resetUrl = 'https://clubinhopetbox.com.br/reset-password?token=' . $token;
        
        $content = '
            <h2>Recuperação de Senha 🔐</h2>
            <p>Olá <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
            <p>Recebemos uma solicitação para redefinir sua senha.</p>
            <p>Clique no botão abaixo para criar uma nova senha:</p>
            <p style="text-align: center;">
                <a href="' . $resetUrl . '" class="button">Redefinir Senha</a>
            </p>
            <p>Este link expira em 1 hora.</p>
            <p>Se você não solicitou esta alteração, ignore este e-mail.</p>
            <p>Equipe CLUBINHO PET 🐾</p>
        ';
        
        return self::send(
            $user['email'],
            'Recuperação de Senha - CLUBINHO PET 🔐',
            self::getTemplate($content)
        );
    }
    
    /**
     * E-mail de notificação de envio
     */
    public static function sendShippingNotification($user, $box) {
        $content = '
            <h2>Sua Box Foi Enviada! 🚚</h2>
            <p>Olá <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
            <p>Boa notícia! Sua box foi enviada e está a caminho! 🎉</p>
            <div style="background: #f0f8ff; padding: 20px; border-radius: 10px; margin: 20px 0;">
                <p><strong>Código de rastreamento:</strong> ' . $box['tracking_code'] . '</p>
                <p><strong>Transportadora:</strong> ' . $box['shipping_company'] . '</p>
                <p><strong>Previsão de entrega:</strong> ' . $box['estimated_delivery'] . '</p>
            </div>
            <p>Prepare-se para ver a alegria do seu pet! 🐾</p>
            <p style="text-align: center;">
                <a href="https://clubinhopetbox.com.br/dashboard/boxes" class="button">Rastrear Pedido</a>
            </p>
            <p>Equipe CLUBINHO PET 🐾</p>
        ';
        
        return self::send(
            $user['email'],
            'Sua Box Foi Enviada! 🚚 - CLUBINHO PET',
            self::getTemplate($content)
        );
    }
}
