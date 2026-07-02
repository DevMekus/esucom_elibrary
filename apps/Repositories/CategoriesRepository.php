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

    private function buildFilters(array $filters){
        $conditions = [];
        $params = [];       

        if (!empty($filters['search'])){
            $conditions[] = "c.category  LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';

        }

        if (!empty($filters['id'])){
            $conditions[] = "c.id = :id";
            $params[':id'] = $filters['id'];
        }

        
        $sql = '';

        if (!empty($conditions)){
            $sql = 'WHERE '. implode(' AND ', $conditions);
        }

        return [
            'sql' => $sql,
            'params' => $params
        ];
    }


    public function paginateOrders(?int $cursor, string $direction, array $filters){
        $paginator = new CursorPaginator($this->connection, 10);
        $filterData = $this->buildFilters($filters);

        $result = $paginator->paginate([
            'table' => $this->table . ' c',
            'column' => 'c.id',
            'cursor' => $cursor,
            'direction' =>  $direction,
            'filters' => $filterData['sql'],
            'params' => $filterData['params'],
        ]);

        $data = empty($result['ids'])
                    ? []
                    : $this->getDataByIds($result['ids']);
        
        return [
            'data' => $data,
            'next_cursor' => $result['next_cursor'],
            'prev_cursor' => $result['prev_cursor'],
            'has_next' => $result['has_next'],
            'has_prev' => $result['has_prev'],
        ];
    }

     /**
     * ✅ STEP 2: FETCH FULL DATA
    */
    private function getDataByIds(array $dataIds): array {

        $placeholders = implode(',', array_fill(0, count($dataIds), '?'));

        $query = "
            SELECT c.* FROM {$this->table} AS c
            WHERE c.id  IN ($placeholders)
            ORDER BY c.category ASC
        ";

        $stmt = $this->connection->prepare($query);
        $stmt->execute($dataIds);

        return $this->hydrateData($stmt->fetchAll(PDO::FETCH_ASSOC));
         
    }

    

    public function hydrateData(array $rows): array {
        $output = [];

        foreach ($rows as $row) {
            $Id = $row['id'];

            // ✅ 1. Create Order ONCE
            if (!isset($output[$Id])) {
                $output[$Id] = [
                    'id' => $Id,
                    'category' => $row['category'],                   
                ];
            }            
        }

       
        return  array_values($output);      
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