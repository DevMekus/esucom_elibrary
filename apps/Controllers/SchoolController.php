<?php
namespace App\Controllers;
use App\Utils\Response;
use App\Utils\RequestValidator;
use App\Services\CategoryService;
use App\Services\DepartmentService;

class SchoolController{

    private CategoryService $category;
    private DepartmentService $department;

    public function __construct()
    {
        $this->category =  new CategoryService();
        $this->department = new DepartmentService();
    }

    public function school(){
        $categories = $this->category->getAll();
        $departments = $this->department->getAll();

        $data = [
            'categories' => $categories,
            'departments' => $departments,
        ];

        Response::success($data, "School information");
    }
}