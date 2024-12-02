<?php

namespace App\Controllers;

use App\Models\TableColumnModel;
use App\Services\ServerObjectsServices;
use lib\ApiResponse;
use Lib\Database\DBObjectsMap;

class TableController extends Controller
{
    protected $conn;
    protected $requireAuth = true;
    private $ServerObjectsServices;

    public function __construct()
    {
        $this->initilize();
        if ($this->conn) {
            $this->ServerObjectsServices = new ServerObjectsServices($this->conn);
        }
    }

    public function details($objId)
    {
        $response = new ApiResponse();

        $data = [
            "tableInfo" => $this->ServerObjectsServices->tableHeaders($objId),
            "columns" => $this->ServerObjectsServices->tableColumns($objId)
        ];

        if (empty($data["tableInfo"])) {
            return $response->Error(500, "Objeto [{$objId}] de tipo Tabla no fue encontrado.");
        }

        return $response->Ok($data);
    }

    public function create($db)
    {
        $serverType = $this->conn->db_server_type;
        $requestStatus = $this->checkRequestParams(["tableName", "schema", "columns"]);
        if (!$requestStatus->success) {
            return $requestStatus;
        }

        if (!$this->isValidServerType($serverType)) {
            $requestStatus->Error(500, "parametro [serverType] contiene un valor invalido");
            return $requestStatus;
        }

        $data = $requestStatus->data;
        //return TableColumnModel::toList($data["columns"]);

        $schema = $data["schema"];
        if (!is_numeric($schema)) {
            $schema = $this->ServerObjectsServices->getObjId($data["schema"], DBObjectsMap::OBJ_SCHEMA);
        }

        if (!is_numeric($db)) {
            $schema = $this->ServerObjectsServices->getObjId($db, DBObjectsMap::OBJ_DATABASE);
        }

        // $response = new ApiResponse();
        // return $response->Ok(TableColumnModel::toList($data["columns"], false));

        return $this->ServerObjectsServices->createTable(
            $db,
            $data["tableName"],
            $schema,
            TableColumnModel::toList($data["columns"], false),
        );
    }
}
