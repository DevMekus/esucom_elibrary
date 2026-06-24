<?php
namespace App\Services;
use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ValidationFailedException;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Utils\Utility;
use PDO;
use configs\Database;
use App\Services\LogService;

class UserService {
    private UserRepository $repo;
    private PDO $db;
    private  LogService $logging;
    public  $new_session;

    public function __construct() {
        $this->db = Database::connect();
        $this->repo = new UserRepository($this->db);  
        $this->logging = new LogService($this->db);        
    }

   

    public function paginateOrders(?int $cursor, string $direction = 'next', $filters ): array{
        //validate direction
        if (!in_array($direction, ['next', 'prev'])){
            $direction = 'next';
        }

        $data = $this->repo->paginateOrders($cursor, $direction, $filters);
        // optional: add metadata layer (useful for frontend)
        

        return [
            'success' => true,
            'data' => $data,            
        ];
    }

    public function getById(string $id){
        if (!$id){
            throw new \InvalidArgumentException("User ID required");
        }

        $getCursor = $this->repo->paginateOrders(null, 'next', ['userid' => $id]);
      
        if(!$getCursor || count($getCursor['data']) == 0){
            return null;
        }

        unset($getCursor['data'][0]['p_sx']); //remove password hash from response

        return $getCursor['data'][0];
    }


    public function hashPassword($plainPassword){
        if (strlen($plainPassword) < 3) {
            throw new \InvalidArgumentException("Password too short");
        }

        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }



    public function verifyPassword($password, $passwordHash){
       
        return password_verify($password, $passwordHash);
    }


    public function generateSessionToken($account){
        //generate and save

        $token = AuthMiddleware::generateToken([
            'userid' => $account['userid'],
            'role' => $account['role'],
            'roleId' => $account['role_id'],
            'branchId' => $account['branch_id'],
            'exp' => time() + 3600 + (12 * 60 * 60) //-> 12 hours //7200 seconds = 2 hours
        ]);

        $this->new_session = [
            'userid' => $account['userid'],
            'token' => $token,
            'ip_address' => Utility::getUserIP(),
            'device' => Utility::getUserDevice(),
        ];

        return $token;
    }




    public function uploadImage(){
        $image = null;

        //Process Images here especially for the staff profile.
        if (
            isset($_FILES['profileImage']) &&
            $_FILES['profileImage']['error'] === UPLOAD_ERR_OK &&
            is_uploaded_file($_FILES['profileImage']['tmp_name'])
        ) {
            $target_dir =   "public/UPLOADS/avatar/";
            $uploadImage = Utility::uploadDocuments('profileImage', $target_dir);
            
            if (!$uploadImage || !$uploadImage['success']) {
                throw new ResourceAlreadyExistsException("Image upload failed");
            } 

            $image = $uploadImage['files'][0];
        }

        return $image;
    }

    
    

    public function create($data){

        if (!isset($data['email_address'], $data['role_id'], $data['user_password'])){
             throw new ValidationFailedException("Missing fields");
        }     
        

        if($this->register($data)){
            
            EmailServices::registrationEmail([
                'fullname' => $data['fullname'],
                'email_address' => $data['email_address'],
                'phone' => $data['phone'] ?? 'N/A',               
                'user_password' => $data['user_password']
            ]); 
            
            return true;
        }
      
    }

    private function register($data): ?int {            

        try {
            $this->db->beginTransaction();
            
            $userId = Utility::generate_uniqueId();
          
            $lastInserId = $this->registerAccount($data, $userId);

            $this->registerProfile($data, $lastInserId);

            $logging = [
                'branch_id' => $data['branch_id'] ?? null,
                'type' => 'registration',
                'title' => 'user registration successful',
                'status' => true,
                'userid'=> $userId
            ];

            $this->logging->create($logging);
            

            $this->db->commit();

            return $lastInserId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Service error while creating new user: " . $originalMessage,
                0,
                $e
            );
        }
    }  

    private function registerProfile($data, $lastInserId){
       $profileImage = $this->uploadImage();

        $userProfile = [
            'account_id' => $lastInserId,                
            'fullname' => $data['fullname'],                
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'city_state' => $data['city_state'] ?? null,               
            'avatar' => $profileImage,        
        ];
        $this->repo->insertProfile($userProfile);
    }
    
    
    private function registerAccount($data, $userId){
        
       $hashedPassword = $this->hashPassword($data['user_password']);

        $acountInfo = [
            'userid' => $userId,
            'branch_id' => $data['branch_id'] ?? null,
            'email_address' => $data['email_address'],
            'user_password' => $hashedPassword,
            'phone' => $data['phone'] ?? null,
            'role_id' => (int) $data['role_id'],            
        ];

       return  $this->repo->insertAccount($acountInfo); //returning lastInsertId
    }

    public function requestPasswordReset($user)  {
        $resetToken = bin2hex(random_bytes(16));
        $resetTokenExpiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $update = [
            'role_id' => $user['role_id'],
            'reset_token' => $resetToken,
            'reset_token_expiration' => $resetTokenExpiration,
        ];

        if ($this->repo->updateAccount($user, $update)) return $update;

        return false;
    }



    public function changePassword($user, string $newPassword) {
        
        $update = [
            'user_password' => $newPassword,
            'reset_token' => null,
            'reset_token_expiration' => null,

        ];
        return $this->repo->updateAccount($user, $update);
    
    }

    private function updateProfileImage($user){
        $image = $user['avatar'];

        if (
            isset($_FILES['profileImage']) &&
            $_FILES['profileImage']['error'] === UPLOAD_ERR_OK &&
            is_uploaded_file($_FILES['profileImage']['tmp_name'])
        ) {

            $target_dir =   "public/UPLOADS/avatar/";

            $user_avatar = Utility::uploadDocuments('profileImage', $target_dir);

            if (!$user_avatar || !$user_avatar['success']){
                throw  new \RuntimeException("Image upload has failed");
            } 

            $image = $user_avatar['files'][0];

            if (isset($user['avatar'])) {
                $filenameFromUrl = basename($user['avatar']);
                $target_dir = "../public/UPLOADS/avatar/" . $filenameFromUrl;
                
                if (file_exists($target_dir))
                    unlink($target_dir);
            }
        }

        return $image;
    }

    public function update($id, $data){
        if (!$id){
            throw new \InvalidArgumentException("User ID required");
        }

        $getCursor = $this->repo->paginateOrders(null, 'next', ['id' => $id]);
      
        if(!$getCursor || count($getCursor['data']) == 0){
            return false;
        }

        $user = $getCursor['data'][0];

        $image = $this->updateProfileImage($user);

        $account = [
            'branch_id' => $data['branch_id'] ?? $user['branch_id'],
            'email_address' => $data['email_address'] ?? $user['email_address'],
            'phone' => $data['phone'] ?? $user['phone'],
            'role_id' => $data['role_id'] ?? $user['role_id'],
            'status' => $data['status'] ?? $user['status'],
        ];

        $profile = [
            'fullname' => $data['fullname'] ?? $user['fullname'],
            'address' => $data['address'] ?? $user['address'],
            'city' => $data['city'] ?? $user['city'],
            'city_state' => $data['state'] ?? $user['state'],
            'avatar' => $image,
        ];

       
        
        $logging = [
                'branch_id' => $account['branch_id'] ?? null,
                'type' => 'update',
                'title' => 'Account update successful',
                'status' => true,
                'userid' => $user['userid']
            ];

           

        try {        

            $this->db->beginTransaction();

                $this->repo->updateAccount($user, $account);
                $this->repo->updateProfile($user['id'], $profile);
                $this->logging->create($logging);

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Service error while updatng a user: " . $originalMessage,
                0,
                $e
            );
        }      
    }

    public function deleteAccount(array $filter, string $password = null){
        
        if (!$filter['id'] && !$filter['userid']){
            throw new \InvalidArgumentException("User ID required");
        }

        $getCursor = $this->repo->paginateOrders(null, 'next', $filter); 
      
        if(!$getCursor || count($getCursor['data']) == 0){
            return false;
        }

        $user = $getCursor['data'][0];
        
        if ($password){
            if (!$this->verifyPassword($password, $user['p_sx'])){
                return false;
            }
        }
        

        if (isset($user['avatar'])) {
            $filenameFromUrl = basename($user['avatar']);
            $target_dir = "../public/UPLOADS/avatars/" . $filenameFromUrl;

            if (file_exists($target_dir)) {
                unlink($target_dir);
            }
        }        

        $logging = [
            'branch_id' => $account['branch_id'] ?? null,
            'type' => 'delete',
            'title' => 'user deleted acccount successful',
            'status' => true,
            'userid' => $user['userid']
        ];


        if (isset($filter['userid'])){
            $id = $filter['userid'];
        } else {
            $id = $filter['id'];
        }        

        
        try {
            $this->db->beginTransaction();
                $this->repo->deleteUser($id);
                $this->logging->create($logging);
            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            $originalMessage = $e->getMessage();
                
                throw new \RuntimeException(
                "Service error while deleting a user: " . $originalMessage,
                0,
                $e
            );
        }      
        

    }

    public function userAnalytics($filter){
        
        $params = [
            ':b' => $filter['branch_id']
        ];

        $userQuery = "
            SELECT
            COUNT(*) as accounts,
            SUM(CASE WHEN a.role_id = '1' THEN 1 ELSE 0 END) as customers,
            SUM(CASE WHEN a.role_id = '2' THEN 1 ELSE 0 END) as manager,
            SUM(CASE WHEN a.role_id = '3' THEN 1 ELSE 0 END) as cashiers
            FROM accounts_tbl a            
        ";

        if (!empty($filter['branch_id'])){
            $userQuery .= " WHERE a.branch_id = :b";
        }

        $userStmt = $this->db->prepare($userQuery);

        if (!empty($filter['branch_id'])){
            $userStmt->bindValue(":b", (int) $filter['branch_id']);
        }
        

        $userStmt->execute();
        $data = $userStmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_users' => (int) ($data['accounts'] ?? 0),
            'total_customers' => (int) ($data['customers'] ?? 0),
            'total_cashiers' => (int) ($data['cashiers'] ?? 0),
            'total_managers' => (int) ($data['managers'] ?? 0),
        ];
    }

    public static function sendGuestMessage(array $data)
    {
         return EmailServices::sendContactusMessageToAdmin($data);
    }


    public function accountMigration(){
        $accountOld = 'accounts_tbl_old';
        $userOld = 'users_old';

        

        $query = "
            SELECT a.*,
            u.userid,
            u.fullname,
            u.email_address,
            u.phone,
            u.user_password,
            u.address,
            u.city,
            u.city_state,
            u.avatar

            FROM {$accountOld} AS a
            LEFT JOIN {$userOld} AS u on u.userid = a.userid
            ORDER BY a.created_at ASC
        
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();       
        $data =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        //  echo json_encode($data); exit;

        foreach ($data as $d) {
            $acountInfo = [
                'userid' => $d['userid'],
                'branch_id' => 1,
                'email_address' => $d['email_address'],
                'user_password' => $d['user_password'],
                'phone' => $d['phone'] ?? null,
                'role_id' => (int) $d['role_id'],            
            ];

          

            $lastInserId = $this->repo->insertAccount($acountInfo); 

           

            $userProfile = [
                'account_id' => $lastInserId,                
                'fullname' => $d['fullname'],                
                'address' => $d['address'] ?? null,
                'city' => $d['city'] ?? null,
                'city_state' => $d['city_state'] ?? null,               
                'avatar' => $d['avatar'] ?? null,        
            ];
            $this->repo->insertProfile($userProfile);
        }

        return true;
    }
}