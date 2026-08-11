<?php
/**
 * CheckoutManager - Gerenciador de URLs de Checkout Externo
 * 
 * Responsável por:
 * - Armazenar e gerenciar URLs de checkout externo
 * - Redirecionar para checkout sem lógica interna
 * - Validar planos
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

class CheckoutManager {
    
    private static $instance = null;
    private $configManager;
    
    /**
     * Mapeamento padrão de planos para URLs de checkout
     */
    private $checkoutUrls = [
        'happy' => 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50',
        'love' => 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66',
        'deluxe' => 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322'
    ];
    
    /**
     * Singleton pattern
     */
    public static function getInstance($configManager = null) {
        if (self::$instance === null) {
            self::$instance = new self($configManager);
        }
        return self::$instance;
    }
    
    /**
     * Construtor
     */
    private function __construct($configManager = null) {
        $this->configManager = $configManager;
    }
    
    /**
     * Obter URL de checkout para um plano
     * 
     * @param string $planId ID do plano (happy, love, deluxe)
     * @return string|null URL de checkout ou null se plano não existir
     */
    public function getCheckoutUrl($planId) {
        // Tentar obter do ConfigManager primeiro
        if ($this->configManager) {
            $url = $this->configManager->get("checkout_urls.{$planId}");
            if ($url) {
                return $url;
            }
        }
        
        // Fallback para URLs padrão
        return $this->checkoutUrls[$planId] ?? null;
    }
    
    /**
     * Obter todas as URLs de checkout
     * 
     * @return array
     */
    public function getAllCheckoutUrls() {
        $urls = [];
        
        foreach (array_keys($this->checkoutUrls) as $planId) {
            $urls[$planId] = $this->getCheckoutUrl($planId);
        }
        
        return $urls;
    }
    
    /**
     * Definir URL de checkout para um plano
     * 
     * @param string $planId
     * @param string $url
     * @return bool
     */
    public function setCheckoutUrl($planId, $url) {
        if (!$this->configManager) {
            return false;
        }
        
        // Validar URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Salvar no ConfigManager
        $this->configManager->set(
            "checkout_urls.{$planId}",
            $url,
            "URL de checkout para plano {$planId}"
        );
        
        return true;
    }
    
    /**
     * Validar se plano existe
     * 
     * @param string $planId
     * @return bool
     */
    public function isValidPlan($planId) {
        return isset($this->checkoutUrls[$planId]) || $this->getCheckoutUrl($planId) !== null;
    }
    
    /**
     * Gerar HTML de botão de checkout
     * 
     * @param string $planId
     * @param string $text Texto do botão
     * @param string $class Classes CSS adicionais
     * @return string HTML do botão
     */
    public function generateCheckoutButton($planId, $text = 'Assinar Agora', $class = '') {
        $url = $this->getCheckoutUrl($planId);
        
        if (!$url) {
            return '';
        }
        
        $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escapedClass = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
        
        return sprintf(
            '<a href="%s" class="btn btn-checkout %s" data-plan="%s">%s</a>',
            $escapedUrl,
            $escapedClass,
            htmlspecialchars($planId, ENT_QUOTES, 'UTF-8'),
            $escapedText
        );
    }
    
    /**
     * Redirecionar para checkout
     * 
     * @param string $planId
     * @return void
     */
    public function redirect($planId) {
        $url = $this->getCheckoutUrl($planId);
        
        if (!$url) {
            http_response_code(404);
            die('Plano não encontrado');
        }
        
        // Registrar redirecionamento se possível
        $this->logRedirect($planId);
        
        // Redirecionar
        header('Location: ' . $url, true, 302);
        exit;
    }
    
    /**
     * Registrar redirecionamento em log
     * 
     * @param string $planId
     */
    private function logRedirect($planId) {
        try {
            $logFile = __DIR__ . '/../logs/checkout_redirects.log';
            $logDir = dirname($logFile);
            
            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $logMessage = "[{$timestamp}] Plan: {$planId} | IP: {$ip} | UA: {$userAgent}\n";
            
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        } catch (Exception $e) {
            // Silenciosamente falhar
        }
    }
    
    /**
     * Obter informações do plano
     * 
     * @param string $planId
     * @return array|null
     */
    public function getPlanInfo($planId) {
        $plans = [
            'happy' => [
                'id' => 'happy',
                'name' => 'Clubinho Pet Happy',
                'description' => 'Box básica com produtos essenciais',
                'items_count' => '4 a 6 itens',
                'price_monthly' => 101.47,
                'features' => [
                    '4 a 6 produtos essenciais',
                    'Brinquedos divertidos',
                    'Petiscos saborosos',
                    'Produtos de higiene',
                    'Ótimo custo-benefício'
                ]
            ],
            'love' => [
                'id' => 'love',
                'name' => 'Clubinho Pet Love',
                'description' => 'Box premium com produtos exclusivos',
                'items_count' => '6 a 8 itens',
                'price_monthly' => 164.97,
                'features' => [
                    '6 a 8 produtos exclusivos',
                    'Brinquedos interativos',
                    'Petiscos premium',
                    'Acessórios de qualidade',
                    'Marcas renomadas'
                ]
            ],
            'deluxe' => [
                'id' => 'deluxe',
                'name' => 'Clubinho Pet Deluxe',
                'description' => 'Box super premium com o que há de melhor',
                'items_count' => '8 a 10 itens',
                'price_monthly' => 253.87,
                'features' => [
                    '8 a 10 produtos de luxo',
                    'Brinquedos importados',
                    'Petiscos gourmet',
                    'Produtos naturais e orgânicos',
                    'Acessórios exclusivos'
                ]
            ]
        ];
        
        return $plans[$planId] ?? null;
    }
}
