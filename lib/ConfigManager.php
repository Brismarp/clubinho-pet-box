<?php
/**
 * ConfigManager v2.0 - Gerenciador Centralizado de Configurações
 * 
 * Características:
 * - Persistência em banco de dados
 * - Cache em memória
 * - Histórico de alterações
 * - Versionamento
 * - Rollback de versões
 * 
 * @author Clubinho Pet Box
 * @version 2.0
 */

class ConfigManager {
    
    private $db;
    private $cache = [];
    private $cacheLoaded = false;
    private $userId;
    private $ipAddress;
    private $userAgent;
    
    /**
     * Construtor
     * 
     * @param PDO $db Conexão com banco de dados
     * @param int $userId ID do usuário (para auditoria)
     */
    public function __construct($db, $userId = null) {
        $this->db = $db;
        $this->userId = $userId;
        $this->ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Obter configuração por chave
     * 
     * @param string $key Chave da configuração
     * @param mixed $default Valor padrão se não encontrado
     * @return mixed Valor da configuração
     */
    public function get($key, $default = null) {
        // Carregar cache se não estiver carregado
        if (!$this->cacheLoaded) {
            $this->loadCache();
        }
        
        // Retornar do cache
        if (isset($this->cache[$key])) {
            $value = $this->cache[$key]['value'];
            $type = $this->cache[$key]['type'];
            return $this->castValue($value, $type);
        }
        
        return $default;
    }
    
    /**
     * Obter todas as configurações públicas
     * 
     * @return array Array com todas as configurações públicas
     */
    public function getAll() {
        if (!$this->cacheLoaded) {
            $this->loadCache();
        }
        
        $result = [];
        foreach ($this->cache as $key => $config) {
            if ($config['is_public']) {
                $result[$key] = $this->castValue($config['value'], $config['type']);
            }
        }
        
        return $result;
    }
    
    /**
     * Obter todas as configurações (incluindo privadas)
     * 
     * @return array Array com todas as configurações
     */
    public function getAllIncludingPrivate() {
        if (!$this->cacheLoaded) {
            $this->loadCache();
        }
        
        $result = [];
        foreach ($this->cache as $key => $config) {
            $result[$key] = $this->castValue($config['value'], $config['type']);
        }
        
        return $result;
    }
    
    /**
     * Definir configuração
     * 
     * @param string $key Chave da configuração
     * @param mixed $value Valor da configuração
     * @param string $type Tipo de dado (string, integer, boolean, json, array)
     * @param string $description Descrição da configuração
     * @param bool $isPublic Se é pública
     * @return bool Sucesso da operação
     */
    public function set($key, $value, $type = 'string', $description = '', $isPublic = false) {
        try {
            // Obter valor antigo para histórico
            $oldValue = $this->get($key);
            
            // Converter valor para string
            $storedValue = $this->stringifyValue($value, $type);
            
            // Verificar se existe
            $exists = $this->exists($key);
            
            if ($exists) {
                // Atualizar
                $stmt = $this->db->prepare("
                    UPDATE settings 
                    SET value = ?, type = ?, description = ?, is_public = ?, version = version + 1, updated_at = NOW()
                    WHERE key = ?
                ");
                $stmt->execute([$storedValue, $type, $description, $isPublic ? 1 : 0, $key]);
                
                // Registrar no histórico
                $this->logChange($key, $oldValue, $value);
            } else {
                // Inserir
                $stmt = $this->db->prepare("
                    INSERT INTO settings (key, value, type, description, is_public, version)
                    VALUES (?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$key, $storedValue, $type, $description, $isPublic ? 1 : 0]);
            }
            
            // Atualizar cache
            $this->cache[$key] = [
                'value' => $storedValue,
                'type' => $type,
                'is_public' => $isPublic,
                'description' => $description
            ];
            
            // Log de atividade
            $this->logActivity('update_setting', 'settings', null, ['key' => $key], ['key' => $key, 'value' => $storedValue]);
            
            return true;
        } catch (Exception $e) {
            error_log("ConfigManager::set() Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deletar configuração
     * 
     * @param string $key Chave da configuração
     * @return bool Sucesso da operação
     */
    public function delete($key) {
        try {
            $stmt = $this->db->prepare("DELETE FROM settings WHERE key = ?");
            $stmt->execute([$key]);
            
            // Remover do cache
            unset($this->cache[$key]);
            
            // Log de atividade
            $this->logActivity('delete_setting', 'settings', null, ['key' => $key], null);
            
            return true;
        } catch (Exception $e) {
            error_log("ConfigManager::delete() Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se configuração existe
     * 
     * @param string $key Chave da configuração
     * @return bool Existe ou não
     */
    public function exists($key) {
        if (!$this->cacheLoaded) {
            $this->loadCache();
        }
        
        return isset($this->cache[$key]);
    }
    
    /**
     * Obter histórico de alterações
     * 
     * @param string $key Chave da configuração
     * @param int $limit Limite de registros
     * @return array Array com histórico
     */
    public function getHistory($key, $limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT sh.*, u.name as changed_by_name
                FROM settings_history sh
                LEFT JOIN users u ON sh.changed_by = u.id
                WHERE sh.setting_id = (SELECT id FROM settings WHERE key = ?)
                ORDER BY sh.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$key, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("ConfigManager::getHistory() Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Fazer rollback para versão anterior
     * 
     * @param string $key Chave da configuração
     * @param int $version Versão para rollback
     * @return bool Sucesso da operação
     */
    public function rollback($key, $version) {
        try {
            // Obter versão anterior
            $stmt = $this->db->prepare("
                SELECT value, type FROM settings_history
                WHERE setting_id = (SELECT id FROM settings WHERE key = ?)
                ORDER BY created_at DESC
                LIMIT 1 OFFSET ?
            ");
            $stmt->execute([$key, $version - 1]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $this->set($key, $result['value'], $result['type']);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("ConfigManager::rollback() Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Limpar cache
     */
    public function clearCache() {
        $this->cache = [];
        $this->cacheLoaded = false;
    }
    
    /**
     * Recarregar cache do banco de dados
     */
    public function reloadCache() {
        $this->clearCache();
        $this->loadCache();
    }
    
    /**
     * Carregar todas as configurações para cache
     * 
     * @private
     */
    private function loadCache() {
        try {
            $stmt = $this->db->query("SELECT * FROM settings");
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($settings as $setting) {
                $this->cache[$setting['key']] = [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'is_public' => (bool)$setting['is_public'],
                    'description' => $setting['description'],
                    'version' => $setting['version']
                ];
            }
            
            $this->cacheLoaded = true;
        } catch (Exception $e) {
            error_log("ConfigManager::loadCache() Error: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar alteração no histórico
     * 
     * @private
     */
    private function logChange($key, $oldValue, $newValue) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO settings_history (setting_id, old_value, new_value, changed_by, ip_address, user_agent)
                SELECT id, ?, ?, ?, ?, ? FROM settings WHERE key = ?
            ");
            $stmt->execute([
                json_encode($oldValue),
                json_encode($newValue),
                $this->userId,
                $this->ipAddress,
                $this->userAgent,
                $key
            ]);
        } catch (Exception $e) {
            error_log("ConfigManager::logChange() Error: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar atividade
     * 
     * @private
     */
    private function logActivity($action, $entityType, $entityId, $oldValues, $newValues) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $this->userId,
                $action,
                $entityType,
                $entityId,
                json_encode($oldValues),
                json_encode($newValues),
                $this->ipAddress,
                $this->userAgent
            ]);
        } catch (Exception $e) {
            error_log("ConfigManager::logActivity() Error: " . $e->getMessage());
        }
    }
    
    /**
     * Converter valor para string para armazenamento
     * 
     * @private
     */
    private function stringifyValue($value, $type) {
        switch ($type) {
            case 'json':
            case 'array':
                return json_encode($value);
            case 'boolean':
                return $value ? '1' : '0';
            case 'integer':
                return (string)(int)$value;
            default:
                return (string)$value;
        }
    }
    
    /**
     * Converter valor para tipo correto
     * 
     * @private
     */
    private function castValue($value, $type) {
        switch ($type) {
            case 'json':
            case 'array':
                return json_decode($value, true);
            case 'boolean':
                return (bool)$value;
            case 'integer':
                return (int)$value;
            default:
                return $value;
        }
    }
}

?>
