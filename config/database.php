<?php
// config/database.php - Configuração do Banco de Dados
// Versão Corrigida - 14/01/2026

class Database {
    private static $instance = null;
    private $connection;
    
    // ============================================
    // CONFIGURAÇÕES DO BANCO - ATUALIZADAS
    // ============================================
    private $host = 'localhost';
    private $database = 'u463356968_clubinhopet';
    private $username = 'u463356968_clubinhopet_us';
    private $password = 'Carpe1936!';  // Senha corrigida
    
    private function __construct() {
        try {
            // Tentar conexão
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            // Log de sucesso
            error_log('✓ Conexão com banco de dados estabelecida');
            
        } catch (PDOException $e) {
            // Log detalhado do erro
            $errorMsg = "ERRO DE CONEXÃO COM BANCO DE DADOS:\n";
            $errorMsg .= "Host: {$this->host}\n";
            $errorMsg .= "Database: {$this->database}\n";
            $errorMsg .= "Username: {$this->username}\n";
            $errorMsg .= "Erro: " . $e->getMessage() . "\n";
            $errorMsg .= "Código: " . $e->getCode() . "\n";
            
            error_log($errorMsg);
            
            // Mensagem amigável para o usuário
            die("
                <!DOCTYPE html>
                <html lang='pt-BR'>
                <head>
                    <meta charset='UTF-8'>
                    <title>Erro de Conexão</title>
                    <style>
                        body { font-family: Arial; padding: 40px; background: #f5f5f5; }
                        .error-box { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; border-left: 5px solid #dc3545; }
                        h1 { color: #dc3545; margin-top: 0; }
                        .details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                        code { background: #e9ecef; padding: 2px 6px; border-radius: 3px; }
                        ul { line-height: 1.8; }
                    </style>
                </head>
                <body>
                    <div class='error-box'>
                        <h1>❌ Erro de Conexão com Banco de Dados</h1>
                        <p>Não foi possível conectar ao banco de dados.</p>
                        
                        <div class='details'>
                            <strong>Credenciais Configuradas:</strong><br>
                            Host: <code>{$this->host}</code><br>
                            Database: <code>{$this->database}</code><br>
                            Username: <code>{$this->username}</code>
                        </div>
                        
                        <h3>Possíveis Causas:</h3>
                        <ul>
                            <li>Senha do banco de dados incorreta</li>
                            <li>Banco de dados não existe</li>
                            <li>Usuário sem permissão</li>
                            <li>Servidor MySQL desligado</li>
                        </ul>
                        
                        <h3>Como Corrigir:</h3>
                        <ol>
                            <li>Acesse o painel de controle da hospedagem</li>
                            <li>Verifique se o banco <code>{$this->database}</code> existe</li>
                            <li>Confirme a senha do banco</li>
                            <li>Verifique se o usuário tem permissão</li>
                            <li>Se necessário, atualize a senha em <code>config/database.php</code></li>
                        </ol>
                        
                        <h3>Erro Técnico:</h3>
                        <div class='details'>
                            <code>" . htmlspecialchars($e->getMessage()) . "</code>
                        </div>
                    </div>
                </body>
                </html>
            ");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevenir clonagem
    private function __clone() {}
    
    // Prevenir unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Testar conexão
     */
    public function testConnection() {
        try {
            $stmt = $this->connection->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Obter informações do banco
     */
    public function getDatabaseInfo() {
        try {
            $info = [];
            
            // Nome do banco
            $stmt = $this->connection->query("SELECT DATABASE() as db_name");
            $info['database'] = $stmt->fetch()['db_name'];
            
            // Versão do MySQL
            $stmt = $this->connection->query("SELECT VERSION() as version");
            $info['mysql_version'] = $stmt->fetch()['version'];
            
            // Listar tabelas
            $stmt = $this->connection->query("SHOW TABLES");
            $info['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $info['table_count'] = count($info['tables']);
            
            return $info;
            
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

// Definir constantes para compatibilidade
define('DB_HOST', 'localhost');
define('DB_NAME', 'u463356968_clubinhopet');
define('DB_USER', 'u463356968_clubinhopet_us');
define('DB_PASS', 'Carpe1936!');
?>
