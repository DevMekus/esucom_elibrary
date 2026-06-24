<?php
namespace App\Services;
use PDO;

class CursorPaginator {
    private PDO $db;
    private int $limit;
    
    public function __construct(PDO $db, int $limit = 10)
    {
        $this->db = $db;
        $this->limit = $limit;
    }

    public function paginate(array $config): array{
        $table = $config['table'];
        $cursor = $config['cursor'] ?? null;
        $direction = $config['direction'] ?? 'next';
        $filters = $config['filters'] ?? '';
        $params = $config['params'] ?? [];
        $column = $config['column'] ?? 'id';

        //Build base query
        $query = "SELECT {$column} FROM {$table} {$filters}";

        //Cursor conditions
        if ($cursor){
            $query .= $filters ? " AND" : " WHERE";

            if ($direction === 'next'){
                $query .= " {$column} < :cursor";
            } else {
                $query .= " {$column} > :cursor";
            }

            $params[':cursor'] = $cursor;
        }

        //ordering
        if ($direction === 'next'){
            $query .= " ORDER BY {$column} DESC";
        } else {
            $query .= " ORDER BY {$column} ASC";
        }

        $query .= " LIMIT :limit";

        $stmt = $this->db->prepare($query);

        foreach ($params as $k => $v){
            $stmt->bindValue($k, $v);
        }

        $stmt->bindValue(':limit', $this->limit + 1, PDO::PARAM_INT);
        $stmt->execute();

        $ids = $stmt->fetchAll(PDO:: FETCH_COLUMN);

        $hasMore = count($ids) > $this->limit;

        if ($hasMore){
            array_pop($ids);
        }

        if ($direction === 'next'){
            $hasNext = $hasMore;
            $hasPrev = $cursor !== null;
        } else {
            $hasPrev = $hasMore;
            $hasNext =  $cursor !== null;
        }

        if ($direction === 'prev') {
            $ids = array_reverse($ids);
        }

        return [
            'ids' => $ids,
            'next_cursor' => end($ids) ?: null,
            'prev_cursor' => reset($ids) ?: null,
            'has_next' => $hasNext,
            'has_prev' => $hasPrev
        ];

        

    }
}