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
            $data['title'],
            $data['author'], 
            $ebookUrl,           
            $data['category_id'],          
        ]);
    }

    public function update(string $id, array $data){
        if (!isset($id)){
            throw new ValidationFailedException('Ebook Id required');
        }
        $getCursor = $this->paginateOrders(null, 'next', ['id' => (int)$id]);
         
        if(!$getCursor || count($getCursor['data']) == 0){
            throw new ResourceNotFoundException("ebook information failed to fetch");
        }

        $ebook = $getCursor['data'][0];

        return $this->repo->update($ebook , $data);
    }

    public function delete(string $id){
        if (!isset($id)){
            throw new ValidationFailedException('Ebook Id required');
        }

        return $this->repo->delete((int)$id);
    }
}