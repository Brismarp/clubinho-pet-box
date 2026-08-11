<?php
/**
 * CLUBINHO PET - Model User
 * Gerenciamento de usuários (tutores)
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

class User {
    
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Criar novo usuário
     */
    public function create($data) {
        // Verificar se e-mail já existe
        if ($this->emailExists($data['email'])) {
            return false;
        }
        
        $userData = [
            'name' => sanitize($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]),
            'phone' => sanitize($data['phone'] ?? ''),
            'document' => sanitize($data['document'] ?? ''),
            'status' => 'active'
        ];
        
        $userId = $this->db->insert('users', $userData);
        
        if ($userId) {
            log_activity('user_registered', $userId, ['email' => $data['email']]);
            
            // Enviar e-mail de boas-vindas
            Mailer::sendWelcome([
                'name' => $data['name'],
                'email' => $data['email']
            ]);
        }
        
        return $userId;
    }
    
    /**
     * Atualizar usuário
     */
    public function update($id, $data) {
        $userData = [];
        
        if (isset($data['name'])) $userData['name'] = sanitize($data['name']);
        if (isset($data['phone'])) $userData['phone'] = sanitize($data['phone']);
        if (isset($data['document'])) $userData['document'] = sanitize($data['document']);
        if (isset($data['birth_date'])) $userData['birth_date'] = $data['birth_date'];
        
        // Endereço
        if (isset($data['address_zipcode'])) $userData['address_zipcode'] = sanitize($data['address_zipcode']);
        if (isset($data['address_street'])) $userData['address_street'] = sanitize($data['address_street']);
        if (isset($data['address_number'])) $userData['address_number'] = sanitize($data['address_number']);
        if (isset($data['address_complement'])) $userData['address_complement'] = sanitize($data['address_complement']);
        if (isset($data['address_neighborhood'])) $userData['address_neighborhood'] = sanitize($data['address_neighborhood']);
        if (isset($data['address_city'])) $userData['address_city'] = sanitize($data['address_city']);
        if (isset($data['address_state'])) $userData['address_state'] = sanitize($data['address_state']);
        
        $success = $this->db->update('users', $userData, 'id = ?', [$id]);
        
        if ($success) {
            log_activity('user_updated', $id);
        }
        
        return $success;
    }
    
    /**
     * Atualizar senha
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
        
        $success = $this->db->update('users', 
            ['password' => $hashedPassword],
            'id = ?',
            [$id]
        );
        
        if ($success) {
            log_activity('password_changed', $id);
        }
        
        return $success;
    }
    
    /**
     * Buscar usuário por ID
     */
    public function findById($id) {
        return $this->db->findById('users', $id);
    }
    
    /**
     * Buscar usuário por e-mail
     */
    public function findByEmail($email) {
        return $this->db->findOne('users', 'email = ?', [strtolower(trim($email))]);
    }
    
    /**
     * Verificar se e-mail existe
     */
    public function emailExists($email) {
        return $this->db->exists('users', 'email = ?', [strtolower(trim($email))]);
    }
    
    /**
     * Autenticar usuário
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Verificar senha
        if (!password_verify($password, $user['password'])) {
            log_activity('login_failed', $user['id'], ['email' => $email]);
            return false;
        }
        
        // Verificar status
        if ($user['status'] !== 'active') {
            return false;
        }
        
        log_activity('login_success', $user['id']);
        
        return $user;
    }
    
    /**
     * Fazer login (criar sessão)
     */
    public function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = false;
        
        // Regenerar ID da sessão para segurança
        session_regenerate_id(true);
        
        return true;
    }
    
    /**
     * Fazer logout
     */
    public function logout() {
        log_activity('logout', $_SESSION['user_id'] ?? null);
        
        // Limpar sessão
        $_SESSION = [];
        
        // Destruir cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir sessão
        session_destroy();
        
        return true;
    }
    
    /**
     * Criar token de recuperação de senha
     */
    public function createPasswordResetToken($email) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Gerar token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Salvar token
        $this->db->insert('password_resets', [
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'used' => false
        ]);
        
        // Enviar e-mail
        Mailer::sendPasswordReset($user, $token);
        
        log_activity('password_reset_requested', $user['id']);
        
        return true;
    }
    
    /**
     * Validar token de recuperação de senha
     */
    public function validatePasswordResetToken($token) {
        $reset = $this->db->findOne('password_resets', 
            'token = ? AND used = ? AND expires_at > NOW()',
            [$token, false]
        );
        
        return $reset !== false;
    }
    
    /**
     * Redefinir senha com token
     */
    public function resetPassword($token, $newPassword) {
        $reset = $this->db->findOne('password_resets',
            'token = ? AND used = ? AND expires_at > NOW()',
            [$token, false]
        );
        
        if (!$reset) {
            return false;
        }
        
        // Buscar usuário
        $user = $this->findByEmail($reset['email']);
        
        if (!$user) {
            return false;
        }
        
        // Atualizar senha
        $this->updatePassword($user['id'], $newPassword);
        
        // Marcar token como usado
        $this->db->update('password_resets',
            ['used' => true],
            'token = ?',
            [$token]
        );
        
        log_activity('password_reset_completed', $user['id']);
        
        return true;
    }
    
    /**
     * Obter total de pontos do usuário
     */
    public function getPoints($userId) {
        $result = $this->db->queryOne(
            "SELECT COALESCE(total_points, 0) as points 
             FROM v_user_points 
             WHERE user_id = ?",
            [$userId]
        );
        
        return $result ? (int)$result['points'] : 0;
    }
    
    /**
     * Obter estatísticas do usuário
     */
    public function getStats($userId) {
        $stats = [
            'total_pets' => $this->db->count('pets', 'user_id = ? AND status = ?', [$userId, 'active']),
            'active_subscriptions' => $this->db->count('subscriptions', 'user_id = ? AND status = ?', [$userId, 'active']),
            'total_boxes' => $this->db->count('boxes', 'user_id = ?', [$userId]),
            'total_points' => $this->getPoints($userId)
        ];
        
        return $stats;
    }
    
    /**
     * Buscar todos os usuários (admin)
     */
    public function findAll($filters = []) {
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $where .= ' AND (name LIKE ? OR email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        return $this->db->findAll('users', $where, $params, 'created_at DESC');
    }
    
    /**
     * Contar usuários
     */
    public function count($filters = []) {
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        return $this->db->count('users', $where, $params);
    }
}
