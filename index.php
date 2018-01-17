<?php

try{
    $dbname = 'sessions';
    $ip = getenv('IP');
    $user = getenv('C9_USER');
    $port = 3306;
   
    $db = new PDO("mysql:host=$ip;dbname=$dbname", $user, '');
    
}catch(PDOException $e){
    die('Failed');
}

class DatabaseSessionHandler implements SessionHandlerInterface{
    
    protected $db;
    
    public function __construct(PDO $db){
        $this->db = $db;
    }
    
    public function read($id){
        $statement = $this->db->prepare("
            SELECT * FROM `sessions`
            WHERE `id` = :id
        ");
        
        $statement->execute(['id' => $id]);
        
        if($row = $statement->fetch(PDO::FETCH_OBJ)) return $row->data;
        
        return '';
    }
    
    public function write($id, $data){
        $statement = $this->db->prepare("
            REPLACE INTO `sessions` VALUES (:id, :timetamp, :data)
        ");
        $insert = $statement->execute([
            'id' => $id,
            'timetamp' => time(),
            'data' => $data
        ]);
        
        if($insert) return true;
        
        return false;
    }
    
    public function open($path, $name){
        if($this->db) return true;
        
        return false;
    }
    
    public function close(){
        $this->db = null;
        
        if($this->db === null) return true; // to understand what we are trying to do
        
        return false;
    }
    
    public function destroy($id){
        $statement = $this->db->prepare("
            DELETE FROM `sessions` WHERE `id` = :id
        ");
        
        $delete = $statement->execute(['id' => $id]);
        
        if($delete) return true;
        
        return false;
    }
    
    public function gc($max){
        $limit = time() - $max;
        
        $statement = $this->db->prepare("
            DELETE FROM `sessions` WHERE `access` < :limit
        ");
        $delete = $statement->execute(['limit' => $limit]);
        
        if($delete) return true;
        
        return false;
    }

}

session_set_save_handler(new DatabaseSessionHandler($db));

session_start();