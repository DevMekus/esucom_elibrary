<?php
namespace App\Repositories;
use App\Utils\Utility;
use App\Services\CursorPaginator;
use PDO;

class OpacRepository{
    private PDO $connection;
    private string $table;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
        $this->table = Utility::$opac_catalog_tbl;
    }


    private function buildFilters(array $filters){
        $conditions = [];
        $params = [];       

        if (!empty($filters['search'])){
            $conditions[] = "o.author LIKE :search OR o.title LIKE :search OR o.publisher LIKE :search";
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
            SELECT o.*,
            c.category

            FROM {$this->table} AS o
            LEFT JOIN category AS c ON c.id = o.category_id
            WHERE o.id  IN ($placeholders)
            ORDER BY o.category_id ASC
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
                    'author' => $row['author'],
                    'title' => $row['title'],
                    'accession_no' => $row['accession_no'],
                    'publisher' => $row['publisher'],
                    'category' => $row['category'],
                    'category_id' => $row['category_id'],
                    'publication_place' => $row['publication_place'],
                    'date_of_publication' => $row['date_of_publication'],
                    'call_number' => $row['call_number'],
                    'serial_number' => $row['serial_number'],
                    'shelve_number' => $row['shelve_number'],
                    'copies' => $row['copies'],
                ];
            }            
        }

       
        return  array_values($output);      
    }
    
    public function create(array $opac){
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO {$this->table} (author, title, accession_no, publisher, category_id, publication_place, date_of_publication, call_number, serial_number, shelve_number, copies) 
                VALUES (:author, :title, :accession_no, :publisher, :category_id, :publication_place, :date_of_publication, :call_number, :serial_number, :shelve_number, :copies)"
            );
            $stmt->bindValue(':author', $opac['author'], \PDO::PARAM_STR);
            $stmt->bindValue(':title', $opac['title'], \PDO::PARAM_STR);
            $stmt->bindValue(':accession_no', $opac['accession_no'], \PDO::PARAM_STR);
            $stmt->bindValue(':publisher', $opac['publisher'], \PDO::PARAM_STR);
            $stmt->bindValue(':category_id', $opac['category_id'], \PDO::PARAM_INT);            
            $stmt->bindValue(':publication_place', $opac['publication_place'], \PDO::PARAM_STR);
            $stmt->bindValue(':date_of_publication', $opac['date_of_publication'], \PDO::PARAM_STR);
            $stmt->bindValue(':call_number', $opac['call_number'], \PDO::PARAM_STR);
            $stmt->bindValue(':serial_number', $opac['serial_number'], \PDO::PARAM_STR);
            $stmt->bindValue(':shelve_number', $opac['shelve_number'], \PDO::PARAM_STR);
            $stmt->bindValue(':copies', $opac['copies'], \PDO::PARAM_INT);

            $stmt->execute();

            $id = $this->connection->lastInsertId();

            if (!$id) {
                throw new \RuntimeException("Insert failed, no ID returned");
            }

            return $id;

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while inserting opac: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function update(array $prev, array $new){
        try {
            $query = "UPDATE {$this->table} 
                SET 
                    author = :author,
                    title = :title,
                    accession_no = :accession_no,
                    publisher = :publisher,
                    category_id = :category_id,
                    publication_place = :publication_place,
                    date_of_publication = :date_of_publication,
                    call_number = :call_number,
                    serial_number = :serial_number,
                    shelve_number = :shelve_number,
                    copies = :copies
                WHERE id = :id";
            
            $stmt = $this->connection->prepare($query);

            $stmt->bindValue('author', $new['author'] ?? $prev['author']);
            $stmt->bindValue(':title', $new['title'] ?? $prev['title']);
            $stmt->bindValue(':accession_no', $new['accession_no'] ?? $prev['accession_no']);
            $stmt->bindValue(':publisher', $new['publisher'] ?? $prev['publisher']);
            $stmt->bindValue(':category_id', $new['category_id'] ?? $prev['category_id']);
            $stmt->bindValue(':publication_place', $new['publication_place'] ?? $prev['publication_place']);
            $stmt->bindValue(':date_of_publication', $new['date_of_publication'] ?? $prev['date_of_publication']);
            $stmt->bindValue(':call_number', $new['call_number'] ?? $prev['call_number']);
            $stmt->bindValue(':serial_number', $new['serial_number'] ?? $prev['serial_number']);
            $stmt->bindValue(':shelve_number', $new['shelve_number'] ?? $prev['shelve_number']);
            $stmt->bindValue(':copies', $new['copies'] ?? $prev['copies']);
            $stmt->bindValue(':id',  $prev['id']);

            return $stmt->execute();  

        } catch (\Throwable $e) {
              $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while updating opac: " . $originalMessage,
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
                "Database error while deleting an opac: " . $originalMessage,
                0,
                $e
            );
        }
    }
    

    public function exist(string $title, string $author){
        try {
            $stmt = $this->connection->prepare("SELECT COUNT(*) FROM {$this->table} 
            WHERE title = :title AND author = :author");
            $stmt->bindValue(':title', $title, \PDO::PARAM_STR);
            $stmt->bindValue(':author', $author, \PDO::PARAM_STR);
            $stmt->execute();
            
        return $stmt->fetchColumn() > 0;

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while checking existence of ebook", 0, $e);
        }
    }
}