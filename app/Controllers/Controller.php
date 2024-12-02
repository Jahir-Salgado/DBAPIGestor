<?php

namespace App\Controllers;

use App\Services\AuthServices;
use lib\ApiResponse;
use Lib\Database\DBSource;

class Controller
{
    /**
     * @var DBSource
     */
    protected $conn;
    protected $publicAccess = [];
    protected $requireAuth = true;

    public function view($route, $data = [])
    {
        extract($data);

        $route = str_replace(".", "/", $route);
        $URLview = "../resource/views/{$route}.php";

        if (file_exists($URLview)) {
            ob_start();
            include $URLview;
            $content = ob_get_clean();

            return $content;
        } else {
            return "El archivo no existe";
        }
    }

    public function initilize()
    {
        $session = AuthServices::validateSession();
        if ($session->success) {
            $this->conn = DBSource::map($session->data);
        }
        return $session;
    }

    public function getPostData(): array
    {
        $parameters = [];

        $rawInput = file_get_contents('php://input');
        $decodedJson = json_decode($rawInput, true);

        if (is_array($decodedJson)) {
            $parameters = array_merge($parameters, $decodedJson);
        }

        if (!empty($_POST)) {
            $parameters = array_merge($parameters, $_POST);
        }

        return $parameters;
    }

    public function checkRequestParams($requiredFields)
    {
        $response = new ApiResponse();

        $requestData = $this->getPostData();
        $requestDataKeys = array_keys($requestData);

        foreach ($requiredFields as $field) {
            if (!in_array($field, $requestDataKeys)) {
                $response->Error(500, "Se esperaba recibir el parametro [{$field}], no fue recibido");
                return $response;
            }
        }

        $response->Ok($requestData);
        return $response;
    }

    public function isValidServerType($serverType)
    {
        return in_array($serverType, ["MSSQL", "MYSQL"]);
    }

    public function isRequireAuth()
    {
        return $this->requireAuth;
    }
}
