<?php
namespace App\Repositories;
use App\Utils\Utility;
use App\Services\CursorPaginator;
use PDO;

class DatabaseRespository{
    private PDO $connection;
    private string $table;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->table = Utility::$research_databases_tbl;
    }

    private function notDeletedCondition(): string
    {
        return "o.deleted_at IS NULL";
    }

    private function buildFilters(array $filters){
        $conditions = ["o.deleted_at IS NULL"];
        $params = [];       

        if (!empty($filters['search'])){
            $conditions[] = "o.customer_name LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';

        }

        if (!empty($filters['id'])){
            $conditions[] = "o.id = :id";
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
            'table' => $this->table . ' o',
            'column' => 'o.id',
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
                    'name' => $row['db_name'],
                    'url' => $row['access_url'],
                    'subjectId' => $row['subject_id'],
                    'is_active' => $row['is_active'],                   
                    'updated_at' => $row['updated_at'],                   
                ];
            }            
        }

       
        return  array_values($output);      
    } 

    public function create(array $database){
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (subject_id, db_name, access_url, is_active) 
                VALUES (:subject_id, :db_name, :access_url, :is_active)"
            );
            $stmt->bindValue(':subject_id', $database['subject_id'], \PDO::PARAM_INT);
            $stmt->bindValue(':db_name', $database['db_name'], \PDO::PARAM_STR);
            $stmt->bindValue(':access_url', $database['access_url'], \PDO::PARAM_STR);
            $stmt->bindValue(':is_active', $database['is_active'], \PDO::PARAM_STR);

            $stmt->execute();

            $id = $this->connection->lastInsertId();

            if (!$id) {
                throw new \RuntimeException("Insert failed, no ID returned");
            }

            return $id;

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while inserting Database: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function update(array $prev, array $new){
        try {
            $query = "UPDATE {$this->table} 
                SET subject_id = :subject_id, 
                    db_name = :db_name, 
                    access_url = :access_url, 
                    is_active = :is_active, 
                    updated_at = NOW()
                WHERE id = :id";
            
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(':subject_id', $new['subject_id'] ?? $prev['subject_id']);
            $stmt->bindValue(':db_name', $new['db_name'] ?? $prev['db_name']);
            $stmt->bindValue(':access_url', $new['access_url'] ?? $prev['access_url']);
            $stmt->bindValue(':is_active', $new['is_active'] ?? $prev['is_active']);
            $stmt->bindValue(':id',  $prev['id']);

            return $stmt->execute();  

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while updating Database: " . $originalMessage,
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
                "Database error while deleting a database: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function exist(string $name, string $url){
        try {
            $smt = $this->connection->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE db_name = :dName OR access_url = :aurl"
                );
            $smt->bindParam(':dName', $name, \PDO::PARAM_STR);
            $smt->bindParam(':aurl', $url, \PDO::PARAM_STR);
            $smt->execute();
            
        return $smt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while checking existence of DB Reasearch", 0, $e);
        }
    }


}