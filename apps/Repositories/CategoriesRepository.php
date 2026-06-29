<?php
namespace App\Repositories;
use App\Utils\Utility;
use App\Services\CursorPaginator;
use PDO;

class CategoriesRepository{
    private PDO $connection;
    private string $table;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->table = Utility::$category_tbl;
    }

    public function findAll(){
        
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} ORDER BY category ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id){
        
        $stmt = $this->connection->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data){
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (category) 
                VALUES (:category)"
            );
            $stmt->bindValue(':category', $data['category'], \PDO::PARAM_STR);
            $stmt->execute();

            $id = $this->connection->lastInsertId();

            if (!$id) {
                throw new \RuntimeException("Insert failed, no ID returned");
            }

            return $id;
        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while inserting category: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function update(array $prev, array $new){
        try {
            $query = "UPDATE {$this->table} 
                SET category  = :category
                WHERE id = :id";
            
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(':category', $new['category'] ?? $prev['category']);            
            $stmt->bindValue(':id',  $prev['id']);

            return $stmt->execute();  

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while updating category: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function delete(int $id)
    {
        try {
            $sql = "DELETE FROM {$this->table}                
                WHERE id = :id";

            $stmt = $this->connection->prepare($sql);

            return $stmt->execute([
                ':id' => $id
            ]);
        } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while deleting a category: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function exist(string $name){
        try {
            $smt = $this->connection->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE category = :category"
                );
            $smt->bindParam(':category', $name, \PDO::PARAM_STR);          
            $smt->execute();
            
        return $smt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while checking existence of category", 0, $e);
        }
    }
}