<?php
namespace App\Repositories;
use App\Utils\Utility;
use App\Services\CursorPaginator;
use PDO;

class JournalsRepository{
    private PDO $connection;
    private string $table;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->table = Utility::$ejournals_tbl;
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
                    'journal_id' => $row['journal_id'],
                    'department_id' => $row['department_id'],
                    'url' => $row['access_url'],
                    'title' => $row['title'],
                    'department_name' => $row['department_name'],
                    'updated_at' => $row['updated_at'],
                ];
            }            
        }

       
        return  array_values($output);      
    }

    public function exist(int $id, string $title){
        try {
            $smt = $this->connection->prepare("SELECT COUNT(*) FROM {$this->table} WHERE title = :title AND department_id = :id");
            $smt->bindParam(':title', $title, \PDO::PARAM_STR);
            $smt->bindParam(':id', $id, \PDO::PARAM_INT);
            $smt->execute();
            
            return $smt->fetchColumn() > 0;
        } catch (\Throwable $th) {
            Utility::log($th->getMessage(), 'error', 'EjournalRepository::ejournalExists', [], $th);
            return false;
        }
    }
    
    public function create(array $ebook){
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (title, author, access_url, subject_id) 
                VALUES (:title, :author, :access_url, :subject_id)"
            );
            $stmt->bindValue(':title', $ebook['title'], \PDO::PARAM_INT);
            $stmt->bindValue(':author', $ebook['author'], \PDO::PARAM_STR);
            $stmt->bindValue(':access_url', $ebook['access_url'], \PDO::PARAM_STR);
            $stmt->bindValue(':subject_id', $ebook['subject_id'], \PDO::PARAM_STR);

            $stmt->execute();

            $id = $this->connection->lastInsertId();

            if (!$id) {
                throw new \RuntimeException("Insert failed, no ID returned");
            }

            return $id;

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while inserting journal: " . $originalMessage,
                0,
                $e
            );
        }
    }


    public function update(array $prev, array $new){
        try {
            $query = "UPDATE {$this->table} 
                SET  
                    department_id  = :department_id ,
                    title = :title,
                    subject_id = :subject_id,
                    access_url  = :access_url
                WHERE journal_id = :id";
            
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(':department_id', $new['department_id'] ?? $prev['department_id']);
            $stmt->bindValue(':title', $new['title'] ?? $prev['title']);
            $stmt->bindValue(':access_url', $new['access_url'] ?? $prev['access_url']);
            $stmt->bindValue(':subject_id', $new['subject_id'] ?? $prev['subject_id']);
            $stmt->bindValue(':id',  $prev['journal_id']);

            return $stmt->execute();  

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while updating journal: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function delete(int $id)
    {
        try {
            $sql = "DELETE FROM {$this->table}                
                WHERE journal_id = :id";

            $stmt = $this->connection->prepare($sql);

            return $stmt->execute([
                ':id' => $id
            ]);
        } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while deleting a journal: " . $originalMessage,
                0,
                $e
            );
        }
    }
}