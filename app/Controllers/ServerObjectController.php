<?php

namespace App\Controllers;

use App\Services\ServerObjectsServices;
use Lib\ApiResponse;

class ServerObjectController extends Controller
{
    protected $requireAuth = true;
    private $ServerObjectsServices;

    public function __construct()
    {
        $this->initilize();
        if ($this->conn) {
            $this->ServerObjectsServices = new ServerObjectsServices($this->conn);
        }
    }

    public function list()
    {
        $response = new ApiResponse();
        return $response->Ok($this->ServerObjectsServices->listServerObjects());
    }

    public function listObjTypes()
    {
        $response = new ApiResponse();
        return $response->Ok($this->ServerObjectsServices->listObjectsTypes());
    }

    public function listDataTypes()
    {
        $response = new ApiResponse();
        return $response->Ok($this->ServerObjectsServices->listDataTypes());
    }

    public function getObjId($objTypeId, $objName)
    {
        $response = new ApiResponse();
        return $response->Ok($this->ServerObjectsServices->getObjId($objName, $objTypeId));
    }
}
