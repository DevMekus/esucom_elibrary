<?php
namespace App\Repositories;
use App\Utils\Utility;
use App\Services\CursorPaginator;
use PDO;

class NotificationRepository{
    private PDO $connection;
    private string $table;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->table = Utility::$notifications_tbl;
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
                    'notice_type' => $row['notice_type'],
                    'send_to' => $row['send_to'],
                    'title' => $row['title'],
                    'message' => $row['message'],
                    'created_at' => $row['created_at'],
                ];
            }            
        }

       
        return  array_values($output);      
    }
    
    public function create(array $notice){
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (notice_type, send_to, title, message) 
                VALUES (:notice_type, :send_to, :title, :message)"
            );
            $stmt->bindValue(':notice_type', $notice['notice_type'], \PDO::PARAM_INT);
            $stmt->bindValue(':send_to', $notice['send_to'], \PDO::PARAM_STR);
            $stmt->bindValue(':title', $notice['title'], \PDO::PARAM_STR);
            $stmt->bindValue(':message', $notice['message'], \PDO::PARAM_STR);

            $stmt->execute();

            $id = $this->connection->lastInsertId();

            if (!$id) {
                throw new \RuntimeException("Insert failed, no ID returned");
            }

            return $id;

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while inserting notification: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function update(array $prev, array $new){
        try {
            $query = "
                UPDATE {$this->table} 
                SET 
                    notice_type = :notice_type,
                    send_to = :send_to,
                    title = :title,
                    message = :message

                WHERE id = :id";
            
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue(':notice_type', $new['notice_type'] ?? $prev['notice_type']);
            $stmt->bindValue(':send_to', $new['send_to'] ?? $prev['send_to']);
            $stmt->bindValue(':title', $new['title'] ?? $prev['title']);
            $stmt->bindValue(':message', $new['message'] ?? $prev['message']);
            $stmt->bindValue(':id',  $prev['id']);

            return $stmt->execute();  

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while updating notification: " . $originalMessage,
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
                "Database error while deleting a ebook: " . $originalMessage,
                0,
                $e
            );
        }
    }    

    public function exist(string $title){
        try {
            $smt = $this->connection->prepare("SELECT COUNT(*) FROM {$this->table} 
            WHERE title = :title");
            $smt->bindValue(':title', $title, \PDO::PARAM_STR);
            $smt->execute();
            
        return $smt->fetchColumn() > 0;

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while checking existence of notification", 0, $e);
        }
    }
}