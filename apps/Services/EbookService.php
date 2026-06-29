<?php
namespace App\Services;

use App\Exceptions\ResourceAlreadyExistsException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationFailedException;
use App\Repositories\EbookRepository;
use App\Utils\Utility;
use configs\Database;
use PDO;

class EbookService{
    private EbookRepository $repo;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->repo =  new EbookRepository($this->db);
    }

    public function paginateOrders(?int $cursor, string $direction = 'next', $filters ): array{
        //validate direction
        if (!in_array($direction, ['next', 'prev'])){
            $direction = 'next';
        }

        $data = $this->repo->paginateOrders($cursor, $direction, $filters);
        // optional: add metadata layer (useful for frontend)

        return $data;
       
    }

    private function validate(array $data){
        if (!isset($data['title'], $data['author'])){
            throw new ValidationFailedException('Ebook Information missing');
        }

        if ($this->repo->exist($data['category_id'], $data['title'])){
            throw new ResourceAlreadyExistsException("ebook already Exists");
        }
    }

    private function handleUpload():string
    {
        $targetDir = "public/UPLOADS/ebooks/";

        $upload = Utility::uploadDocuments('ebook_file', $targetDir);        

        if (!$upload['success'] || !$upload['files']) {
            throw new \RuntimeException("File upload failed");
        }

        return $upload['files'][0];  
    }

    
    public function create(array $data){    
        $this->validate($data);
        
        $ebookUrl = $this->handleUpload();

        return $this->repo->create([
            'title' => $data['title'],
            'author' => $data['author'], 
            'access_url' => $ebookUrl,           
            'category_id' =>  $data['category_id'],          
        ]);
    }

    private function handleUpdateUpload(array $data):string
    {
        $url = $data['url'];

       
        $file = $_FILES['ebook_file'] ?? null;

        $hasFile = $file &&
            (
                (is_array($file['name']) && !empty($file['name'][0]) && $file['error'][0] === UPLOAD_ERR_OK) ||
                (!is_array($file['name']) && !empty($file['name']) && $file['error'] === UPLOAD_ERR_OK)
            );

        if (
            $hasFile
        ) {

            $targetDir = "public/UPLOADS/ebooks/";
            
            $fileUrl = Utility::uploadDocuments('ebook_file', $targetDir);

            if (!$fileUrl || !$fileUrl['success']) {
                throw new ValidationFailedException('file upload failed');
            }

            $url  = $fileUrl['files'][0];    
            
            if (!empty($data['url'])) {                   
                $filePath = __DIR__ . "/../../public/UPLOADS/ebooks/" . basename($data['url']);
                if (file_exists($filePath)) unlink($filePath);
            }
        } 

        return $url;  
        
    }

    public function update(int $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('Ebook Id required');
        }

        $getCursor = $this->paginateOrders(null, 'next', ['rowid' => $id]);
         
        if(!$getCursor || count($getCursor['data']) == 0){
            throw new ResourceNotFoundException("ebook information failed to fetch");
        }

        $ebook = $getCursor['data'][0]; 
        
        $url = $this->handleUpdateUpload($data);

        $newData = [
            'id' => $id,
            'title' => $data['title'] ?? $ebook['title'],
            'author' => $data['author'] ?? $ebook['author'],
            'url' => $url,
            'category' => $data['category'] ?? $ebook['category'],
            'category_id' => $data['category_id'] ?? $ebook['category_id'],
            
        ];
       

        return $this->repo->update($newData);
    }

    public function delete(string $id){
        if (!isset($id)){
            throw new ValidationFailedException('Ebook Id required');
        }

        $getCursor = $this->paginateOrders(null, 'next', ['rowid' => $id]);
         
        if(!$getCursor || count($getCursor['data']) == 0){
            throw new ResourceNotFoundException("ebook information failed to fetch");
        }

        $data = $getCursor['data'][0]; 

        if (!empty($data['url'])) {                   
            $filePath = __DIR__ . "/../../public/UPLOADS/ebooks/" . basename($data['url']);
            if (file_exists($filePath)) unlink($filePath);
        }

        return $this->repo->delete((int)$id);
    }
}