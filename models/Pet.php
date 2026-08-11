<?php
/**
 * CLUBINHO PET - Model Pet
 * Gerenciamento de pets
 * 
 * @package ClubinhoPet
 * @version 1.0.0
 */

class Pet {
    
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Criar novo pet
     */
    public function create($data) {
        $petData = [
            'user_id' => $data['user_id'],
            'name' => sanitize($data['name']),
            'type' => $data['type'],
            'breed' => sanitize($data['breed'] ?? ''),
            'size' => $data['size'] ?? null,
            'age' => $data['age'] ?? null,
            'weight' => $data['weight'] ?? null,
            'gender' => $data['gender'] ?? null,
            'photo' => $data['photo'] ?? null,
            'status' => 'active'
        ];
        
        $petId = $this->db->insert('pets', $petData);
        
        if ($petId) {
            log_activity('pet_created', $data['user_id'], ['pet_id' => $petId, 'name' => $data['name']]);
        }
        
        return $petId;
    }
    
    /**
     * Atualizar pet
     */
    public function update($id, $data) {
        $petData = [];
        
        if (isset($data['name'])) $petData['name'] = sanitize($data['name']);
        if (isset($data['breed'])) $petData['breed'] = sanitize($data['breed']);
        if (isset($data['size'])) $petData['size'] = $data['size'];
        if (isset($data['age'])) $petData['age'] = $data['age'];
        if (isset($data['weight'])) $petData['weight'] = $data['weight'];
        if (isset($data['gender'])) $petData['gender'] = $data['gender'];
        if (isset($data['photo'])) $petData['photo'] = $data['photo'];
        if (isset($data['status'])) $petData['status'] = $data['status'];
        
        $success = $this->db->update('pets', $petData, 'id = ?', [$id]);
        
        if ($success) {
            log_activity('pet_updated', null, ['pet_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Buscar pet por ID
     */
    public function findById($id) {
        return $this->db->findById('pets', $id);
    }
    
    /**
     * Buscar pets de um usuário
     */
    public function findByUserId($userId) {
        return $this->db->findAll('pets', 'user_id = ? AND status = ?', [$userId, 'active'], 'created_at DESC');
    }
    
    /**
     * Buscar todos os pets (admin)
     */
    public function findAll($filters = []) {
        $where = '1=1';
        $params = [];
        
        if (!empty($filters['type'])) {
            $where .= ' AND type = ?';
            $params[] = $filters['type'];
        }
        
        if (!empty($filters['status'])) {
            $where .= ' AND status = ?';
            $params[] = $filters['status'];
        }
        
        return $this->db->findAll('pets', $where, $params, 'created_at DESC');
    }
    
    /**
     * Deletar pet
     */
    public function delete($id) {
        $success = $this->db->delete('pets', 'id = ?', [$id]);
        
        if ($success) {
            log_activity('pet_deleted', null, ['pet_id' => $id]);
        }
        
        return $success;
    }
    
    /**
     * Contar pets de um usuário
     */
    public function countByUserId($userId) {
        return $this->db->count('pets', 'user_id = ? AND status = ?', [$userId, 'active']);
    }
    
    /**
     * Verificar se pet pertence ao usuário
     */
    public function belongsToUser($petId, $userId) {
        $pet = $this->findById($petId);
        return $pet && $pet['user_id'] == $userId;
    }
    
    /**
     * Obter estatísticas de pets
     */
    public function getStats() {
        $stats = [
            'total' => $this->db->count('pets', 'status = ?', ['active']),
            'dogs' => $this->db->count('pets', 'type = ? AND status = ?', ['dog', 'active']),
            'cats' => $this->db->count('pets', 'type = ? AND status = ?', ['cat', 'active'])
        ];
        
        return $stats;
    }
}
