<?php
namespace App\Repositories;
use App\Services\CursorPaginator;
use App\Utils\Utility;
use PDO;

class UserRepository {
    private string $accountTbl;
    private string $userTbl;
    private string $rolesTbl;
    private PDO $dbConnection;
    private string $sessionTbl;

    public function __construct(PDO $db)
    {
        $this->dbConnection = $db;
        $this->accountTbl = Utility::$accounts;
        $this->userTbl = Utility::$users;
        $this->rolesTbl = Utility::$roles;
        $this->sessionTbl =  Utility::$sessions_tbl;
    }    


    private function buildFilters(array $filters){
        $conditions = ["a.deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['search'])){
            $conditions[] = "a.email_address LIKE :search OR a.userid LIKE :search OR a.phone LIKE :search";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['id'])) {
            $conditions[] = "a.id = :id";
            $params[':id'] = (int) $filters['id'];
        }

        if (!empty($filters['branch_id'])) {
            $conditions[] = "a.branch_id = :branch_id";
            $params[':branch_id'] = (int) $filters['branch_id'];
        }

        if (!empty($filters['authenticate'])) {
            $conditions[] = "a.email_address = :auth OR a.phone = :auth";
            $params[':auth'] = $filters['authenticate'];
        }
        
        if (!empty($filters['token'])) {
            $conditions[] = "a.reset_token = :token";
            $params[':token'] =  $filters['token'];
        }


        if (!empty($filters['userid'])) {
            $conditions[] = "a.userid = :userid";
            $params[':userid'] =  $filters['userid'];
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
            'table' => $this->accountTbl . ' a',
            'column' => 'a.id',
            'cursor' => $cursor,
            'direction' =>  $direction,
            'filters' => $filterData['sql'],
            'params' => $filterData['params'],
        ]);

        $total_users = count($result['ids']);
       

        $fetch_result = empty($result['ids'])
                    ? []
                    : $this->getUsersByIds($result['ids'], $direction );
        
        return [
            'data' => $fetch_result,
            'total' => $total_users,
            'next_cursor' => $result['next_cursor'],
            'prev_cursor' => $result['prev_cursor'],
            'has_next' => $result['has_next'],
            'has_prev' => $result['has_prev'],
        ];
    }

    private function getUsersByIds(array $ids, $direction ): array {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $query = "
            SELECT 
                a.*,
                b.branch_name,               
                u.account_id,
                u.fullname,
                u.home_address,
                u.city,
                u.city_state, 
                u.avatar, 
                r.role

            FROM {$this->accountTbl} a
            LEFT JOIN {$this->userTbl} u
                ON a.id = u.account_id

            LEFT JOIN branches b
                ON a.branch_id = b.id

            LEFT JOIN {$this->rolesTbl} r
                ON a.role_id = r.id

            WHERE a.id  IN ($placeholders)
            AND {$this->notDeletedCondition()}
            ORDER BY a.id " . ($direction === 'next' ? 'DESC' : 'ASC')
        ;

        $stmt = $this->dbConnection->prepare($query);
        $stmt->execute($ids);

        return $this->hydrateUserData($stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    private function notDeletedCondition(): string
    {
        return "a.deleted_at IS NULL";
    }

    private function hydrateUserData(array $rows): array {
        $users = [];
        
            foreach ($rows as $row) {
                $sID = $row['id'];

                if (!isset($users[$sID])) {
                   $users[$sID] = [
                        'id' => $sID,
                        'userid' => $row['userid'],
                        'branch_id' => $row['branch_id'],
                        'branch_name' => $row['branch_name'],
                        'fullname' => $row['fullname'],
                        'email_address' => $row['email_address'],
                        'p_sx' => $row['user_password'],
                        'phone' => $row['phone'],
                        'address' => $row['home_address'],
                        'city' => $row['city'],
                        'state' => $row['city_state'],
                        'avatar' => $row['avatar'],
                        'role' => $row['role'],                       
                        'status' => $row['status'],
                        'role_id' => $row['role_id'],
                        'created_at' => $row['created_at'],
                        'reset_token' => $row['reset_token'],
                        'reset_token_expiration' => $row['reset_token_expiration'],
                        
                    ];
                }
            }

        return  array_values($users);
    }


    public function insertAccount(array $account){
      
        try {
            $stmt =  $this->dbConnection->prepare(
                "INSERT INTO {$this->accountTbl} (userid, branch_id, email_address, user_password, phone, status, role_id) VALUES (:userid, :branch_id, :email_address, :user_password, :phone, :status, :role_id)"
            );
            $stmt->bindValue(':userid', $account['userid'], \PDO::PARAM_STR);          
            $stmt->bindValue(':branch_id', (int)$account['branch_id'], \PDO::PARAM_INT);          
            $stmt->bindValue(':email_address', $account['email_address'], \PDO::PARAM_STR); 
            $stmt->bindValue(':user_password', $account['user_password'], \PDO::PARAM_STR); 
            $stmt->bindValue(':phone', $account['phone'], \PDO::PARAM_STR); 
            $stmt->bindValue(':status', $account['status'] ?? 'active', \PDO::PARAM_STR); 
            $stmt->bindValue(':role_id', (int) $account['role_id'] ?? 1, \PDO::PARAM_INT);
            $stmt->execute();

            $id = $this->dbConnection->lastInsertId();

            if (!$id) {
                throw new \RuntimeException("Insert failed, no ID returned");
            }

            return $id;

        } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while creating new user account: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function updateAccount(array $prev, array $account){
       
        try {
           $stmt = $this->dbConnection->prepare(
                "UPDATE {$this->accountTbl}
                SET 
                    role_id  = :role_id,
                    email_address  = :email_address,
                    user_password  = :user_password,
                    phone  = :phone,
                    branch_id  = :branch_id,
                    reset_token  = :reset_token,
                    reset_token_expiration  = :reset_token_expiration,
                    status  = :status 
                  
                WHERE userid = :id"
            );
           
            $stmt->bindValue(':branch_id', $account['branch_id'] ?? $prev['branch_id']);
            $stmt->bindValue(':role_id', $account['role_id'] ?? $prev['role_id']);
            $stmt->bindValue(':email_address', $account['email_address'] ?? $prev['email_address']);
            $stmt->bindValue(':user_password', $account['user_password'] ?? $prev['p_sx']);
            $stmt->bindValue(':phone', $account['phone'] ?? $prev['phone']);          
            $stmt->bindValue(':reset_token', $account['reset_token'] ?? $prev['reset_token']);
            $stmt->bindValue(':reset_token_expiration', $account['reset_token_expiration'] ?? $prev['reset_token_expiration']);
            $stmt->bindValue(':status', $account['status'] ?? $prev['status']);
            $stmt->bindValue(':id', $prev['userid']);

            return $stmt->execute();

         } catch (\PDOException $e) {
                $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Database error while updating user account: " . $originalMessage,
                0,
                $e
            );
        }
    }

    public function insertProfile(array $user){
        try {
            $stmt =  $this->dbConnection->prepare(
                "INSERT IGNORE INTO {$this->userTbl} (account_id, fullname, home_address, city, city_state, avatar) 
                VALUES (:account_id, :fullname, :home_address, :city, :city_state, :avatar)"
            );
            
            $stmt->bindValue(':account_id', (int)$user['account_id'], \PDO::PARAM_INT);           
            $stmt->bindValue(':fullname', $user['fullname'], \PDO::PARAM_STR);           
            $stmt->bindValue(':home_address', $user['address'], \PDO::PARAM_STR);
            $stmt->bindValue(':city', $user['city'], \PDO::PARAM_STR);
            $stmt->bindValue(':city_state', $user['city_state'], \PDO::PARAM_STR);
            $stmt->bindValue(':avatar', $user['avatar'], \PDO::PARAM_STR);

            $stmt->execute();

            $id = $this->dbConnection->lastInsertId();

            if (!$id) {
                throw new \RuntimeException("Insert failed, no ID returned");
            }

            return $id;

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while creating new user profile", 0, $e);
        }
    }

    public function updateProfile($accountId, array $profile){

        try {
             $stmt = $this->dbConnection->prepare(
                "UPDATE {$this->userTbl}
                SET 
                    fullname = :fullname,                    
                    home_address  = :uaddress,
                    city  = :city,                   
                    city_state  = :cstate,
                    avatar  = :avatar                   
                WHERE account_id = :id"
            );
            
            $stmt->bindValue(':fullname',  $profile['fullname']);
            $stmt->bindValue(':uaddress',  $profile['address']);
            $stmt->bindValue(':city',  $profile['city']);
            $stmt->bindValue(':cstate',  $profile['city_state']);
            $stmt->bindValue(':avatar',  $profile['avatar']);
            $stmt->bindValue(':id', (int)$accountId);

            return $stmt->execute();

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while updating a user profile", 0, $e);
        }
    }

    public function saveNewSession($session){
        
        try {
            $stmt =  $this->dbConnection->prepare(
                "INSERT INTO {$this->sessionTbl} (userid, session_token, device, ip_address) VALUES (:userid, :session_token, :device, :ip_address)"
            );

            $stmt->bindValue(':userid', $session['userid'], \PDO::PARAM_STR);
            $stmt->bindValue(':session_token', $session['token'], \PDO::PARAM_STR);
            $stmt->bindValue(':ip_address', $session['ip_address'], \PDO::PARAM_STR);
            $stmt->bindValue(':device', $session['device'], \PDO::PARAM_STR);           
            
            $stmt->execute();
            $stmt->rowCount() > 0 ? $session : null;

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while saving new user session", 0, $e);
        }
    }

    public function destroySession($id){
       
        try {
            $stmt = $this->dbConnection->prepare(
                "DELETE FROM {$this->sessionTbl}  WHERE userid = :id"            
            );
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while clearing user session", 0, $e);
        }
    }


    public function deleteUser($id){
       
        try {
            $stmt = $this->dbConnection->prepare(
                "UPDATE {$this->accountTbl} SET deleted_at = NOW() 
                    WHERE id = :id OR userid = :id"            
            );
            
            $stmt->bindParam(':id', $id);
            return $stmt->execute();

        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error while clearing user session", 0, $e);
        }
    }
}