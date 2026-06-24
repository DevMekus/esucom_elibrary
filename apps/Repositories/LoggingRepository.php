<?php
namespace App\Repositories;
use App\Models\Logging;
use App\Utils\Utility;
use PDO;
use App\Services\CursorPaginator;

class LoggingRepository{

    private string $logTable;
    private PDO $dbConnection;

    public function __construct(PDO $db)
    {
        $this->dbConnection = $db;
        $this->logTable = Utility::$loginactivity;
    }

    

    private function buildFilters(array $filters){
        $conditions = [];
        $params = [];

        if (!empty($filters['id'])) {
            $conditions[] = "l.id = :id OR l.userid = :id";
            $params[':id'] = (int) $filters['id'];
        }

        if (!empty($filters['branch_id'])) {
            $conditions[] = "l.branch_id = :branch_id";
            $params[':branch_id'] = (int) $filters['branch_id'];
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
        $paginator = new CursorPaginator($this->dbConnection, 10);
        $filterData = $this->buildFilters($filters);

        $result = $paginator->paginate([
            'table' => $this->logTable . ' l',
            'column' => 'l.id',
            'cursor' => $cursor,
            'direction' =>  $direction,
            'filters' => $filterData['sql'],
            'params' => $filterData['params'],
        ]);

        $total_users = count($result['ids']);
       

        $fetch_result = empty($result['ids'])
                    ? []
                    : $this->getAllDataByIds($result['ids'], $direction );
        
        return [
            'data' => $fetch_result,
            'total' => $total_users,
            'next_cursor' => $result['next_cursor'],
            'prev_cursor' => $result['prev_cursor'],
            'has_next' => $result['has_next'],
            'has_prev' => $result['has_prev'],
        ];
    }


    private function getAllDataByIds(array $ids, $direction ): array {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $query = "
            SELECT 
                l.*,
                b.branch_name,
                a.id as accoundId,
                u.fullname

            FROM {$this->logTable} l

            LEFT JOIN branches AS b
            ON l.branch_id = b.id 

            LEFT JOIN accounts_tbl AS a
            ON l.userid = a.userid
            
            LEFT JOIN users_profile u
            ON a.id = u.account_id            

            WHERE l.id  IN ($placeholders)            
            ORDER BY l.id " . ($direction === 'next' ? 'DESC' : 'ASC')
        ;

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute($ids);

        return $this->hydrateFetchedData($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    private function hydrateFetchedData(array $rows): array {
        $data = [];
        
            foreach ($rows as $row) {
                $sID = $row['id'];

                if (!isset($data[$sID])) {
                   $data[$sID] = [
                        'id' => $sID,                        
                        'branch_id' => $row['branch_id'],
                        'userid' => $row['userid'],
                        'fullname' => $row['fullname'],
                        'branch' => $row['branch_name'],
                        'log_type' => $row['log_type'],
                        'title' => $row['title'],
                        'period' => $row['period'],                       
                        'status' => $row['status'],
                        'ip' => $row['ip'],
                        'device' => $row['device'],
                    ];
                }
            }

        return  array_values($data);
    }
    
   

    public function create(array $log){

        $stmt =  $this->dbConnection->prepare(
            "INSERT INTO {$this->logTable} (userid, branch_id, log_type, title, status, ip, device) VALUES (:userid, :branch_id, :log_type, :title, :status, :ip, :device)"
        );

        $stmt->bindValue(':userid', $log['userid'], \PDO::PARAM_STR);
        $stmt->bindValue(':branch_id', (int) $log['branch_id'], \PDO::PARAM_STR);
        $stmt->bindValue(':log_type', $log['type'], \PDO::PARAM_STR);
        $stmt->bindValue(':title', $log['title'], \PDO::PARAM_STR);
        $stmt->bindValue('status', $log['status'], \PDO::PARAM_STR);
        $stmt->bindValue(':ip', $log['ip'], \PDO::PARAM_STR);
        $stmt->bindValue(':device', $log['device'], \PDO::PARAM_STR);    
        

        $stmt->execute();

        $id = $this->dbConnection->lastInsertId();

        if (!$id) {
            throw new \RuntimeException("Insert failed, no ID returned");
        }

        return $id;
    }

    public function delete($id){
        try {

            $stmt = $this->dbConnection->prepare(
                "UPDATE {$this->logTable} SET deleted_at = NOW() WHERE id = :id"            
            );

            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);

            return $stmt->execute();
            
         } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while deleting log by id: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function clearBranchLogs($branchId){
        try {

            $stmt = $this->dbConnection->prepare(
                "UPDATE {$this->logTable} SET deleted_at = NOW() WHERE branch_id = :id"            
            );

            $stmt->bindParam(':id', $branchId, \PDO::PARAM_INT);

            return $stmt->execute();
            
         } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while deleting log by branch_id: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function clearAll(){
        try {
            
            $stmt = $this->dbConnection->prepare(
                "DELETE FROM {$this->logTable}"            
            );

            return $stmt->execute();
            
         } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while clearing logs: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function findRecentLogs(){
       try {
        $query = "
            SELECT l.log_type,
                l.title,
                l.period,                
                b.branch_name,
                a.id as accoundId,
                u.fullname
            FROM {$this->logTable} AS l

            LEFT JOIN branches AS b
            ON l.branch_id = b.id 

            LEFT JOIN accounts_tbl AS a
            ON l.userid = a.userid
            
            LEFT JOIN users_profile u
            ON a.id = u.account_id
            ORDER BY l.id DESC limit 7
        ";
        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
       } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while fetching recent logs: " . $originalMessage,
                0,
                $e
            );
        }
    }
}