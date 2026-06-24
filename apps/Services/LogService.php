<?php
namespace App\Services;
use App\Repositories\LoggingRepository;
use App\Utils\Utility;
use PDO;

class LogService {

    private LoggingRepository $repo;

    public function __construct(PDO $db)
    {
        $this->repo = new LoggingRepository($db);
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
    

    public  function create(array $data){        

        if (!$data['type']){
           throw new \InvalidArgumentException("Specify logging type");
        }

        if (!$data['title']){
           throw new \InvalidArgumentException("Specify logging title");
        }

        $log = [
            'branch_id' => $data['branch_id'] ?? null,
            'type' => $data['type'],
            'title' => $data['title'],
            'status' => $data['status'] ?? 'success',
            'userid' => $data['userid'] ?? null,
            'ip' => Utility::getUserIP(),
            'device' => Utility::getUserDevice(),
        ];

        return $this->repo->create($log);
    }

    public function delete(string $id){
        if (!$id){
           throw new \InvalidArgumentException("Log Id required");
        }       

        return $this->repo->delete($id);       
    }

    public function getRecentLogs(){
       return $this->repo->findRecentLogs();
    }
    
}