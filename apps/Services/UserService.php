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
    public  array $new_session;

    public function __construct() {
        $this->db = Database::connect();
        $this->repo = new UserRepository($this->db);  
        $this->logging = new LogService($this->db);        
    }

   

    public function paginateOrders(?int $cursor, string $direction = 'next',array  $filters ): array{
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


    public function hashPassword(string $plainPassword){
        if (strlen($plainPassword) < 3) {
            throw new \InvalidArgumentException("Password too short");
        }

        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }



    public function verifyPassword(string $password, string $passwordHash){
       
        return password_verify($password, $passwordHash);
    }


    public function generateSessionToken(array $account){
        //generate and save

        $token = AuthMiddleware::generateToken([
            'userid' => $account['userid'],
            'role' => $account['role'],
            'roleId' => $account['role_id'],           
            'exp' => time() + 3600 + (12 * 60 * 60) //-> 12 hours //7200 seconds = 2 hours
        ]);

        $this->new_session = [
            'userid' => $account['userid'],
            'token' => $token,
            'ip_address' => Utility::getUserIP(),
            'device' => Utility::getUserDevice(),
        ];

        //Save a session here
        $this->repo->saveNewSession($this->new_session);

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

    
    

    public function create(array $data){

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

    private function register(array $data): ?int {            

        try {
            $this->db->beginTransaction();
            
            $userId = Utility::generate_uniqueId();
          
            $lastInserId = $this->registerAccount($data, $userId);

            $this->registerProfile($data, (int)$lastInserId);
            $this->logging->create([               
                'type' => 'registration',
                'title' => 'user registration successful',
                'status' => true,
                'userid'=> $userId
            ]);
            

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

    private function registerProfile(array $data, int $lastInserId){

       $profileImage = $this->uploadImage();

        $userProfile = [
            'account_id' => $lastInserId,                
            'fullname' => $data['fullname'],                
            'address' => $data['address'] ?? null,
            'department' => $data['department'] ?? null,
            'level' => $data['level'] ?? null,               
            'avatar' => $profileImage,        
        ];
        return $this->repo->insertProfile($userProfile);
    }
    
    
    private function registerAccount(array $data, string $userId){
        
       $hashedPassword = $this->hashPassword($data['user_password']);

       return  $this->repo->insertAccount([
            'userid' => $userId,           
            'email_address' => $data['email_address'],
            'user_password' => $hashedPassword,
            'phone' => $data['phone'] ?? null,
            'role_id' => (int) $data['role_id'],            
        ]); //returning lastInsertId
    }

    public function requestPasswordReset(array $user)  {
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



    public function changePassword(array $user, string $newPassword) {
        
        $update = [
            'user_password' => $newPassword,
            'reset_token' => null,
            'reset_token_expiration' => null,

        ];
        return $this->repo->updateAccount($user, $update);
    
    }

    private function updateProfileImage(array $user){
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

    public function update(int $id, array $data){
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
            'email_address' => $data['email_address'] ?? $user['email_address'],
            'phone' => $data['phone'] ?? $user['phone'],
            'role_id' => $data['role_id'] ?? $user['role_id'],
            'status' => $data['status'] ?? $user['status'],
        ];

        $profile = [
            'fullname' => $data['fullname'] ?? $user['fullname'],
            'address' => $data['address'] ?? $user['address'],
            'department' => $data['department'] ?? $user['department'],
            'level' => $data['level'] ?? $user['level'],
            'avatar' => $image,
        ]; 

        try {        

            $this->db->beginTransaction();

                $this->repo->updateAccount($user, $account);
                $this->repo->updateProfile($user['id'], $profile);
                $this->logging->create([                 
                    'type' => 'update',
                    'title' => 'Account update successful',
                    'status' => true,
                    'userid' => $user['userid']
                ]);

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

        if (isset($filter['userid'])){
            $id = $filter['userid'];
        } else {
            $id = $filter['id'];
        }        

        
        try {
            $this->db->beginTransaction();
                $this->repo->deleteUser($id);
                $this->logging->create([           
                    'type' => 'delete',
                    'title' => 'user deleted acccount successful',
                    'status' => true,
                    'userid' => $user['userid']
                ]);
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
    

    public static function sendGuestMessage(array $data)
    {
         return EmailServices::sendContactusMessageToAdmin($data);
    }
    
}