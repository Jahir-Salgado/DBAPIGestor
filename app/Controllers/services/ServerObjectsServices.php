<?php

namespace App\Services;

use Lib\ApiResponse;
use lib\Database\DataBaseInstance;
use Lib\Database\DBObjectsMap;
use Lib\Database\DBSource;
use Lib\Database\ScriptsDataBase;
use App\Models\TableColumnModel;

class ServerObjectsServices
{
    private $db;

    public function __construct(DBSource $conn)
    {
        $this->db = DataBaseInstance::connect($conn);
    }

    #region serverObjects y objectTypes
    public function listServerObjects()
    {
        $schema = $this->db->mainSchema();
        $query =
            "SELECT 
                T1.OBJ_TYPE_ID, T1.OBJ_TYPE_NAME,
                T0.OBJ_ID, T0.OBJ_NAME, 
                T0.OBJ_SCHEMA_ID, T0.OBJ_SCHEMA_NAME,
                T2.OBJ_ID AS PARENT_ID, T2.OBJ_NAME AS PARENT_NAME, T3.OBJ_TYPE_NAME AS PARENT_TYPE
            FROM {$schema}.TBL_SERVERS_OBJECTS AS T0
            INNER JOIN {$schema}.TBL_OBJECTS_TYPES AS T1 ON T1.OBJ_TYPE_ID = T0.OBJ_TYPE_ID
            LEFT JOIN {$schema}.TBL_SERVERS_OBJECTS AS T2 ON T2.OBJ_ID = T0.OBJ_PARENT_OF
            LEFT JOIN {$schema}.TBL_OBJECTS_TYPES AS T3 ON T3.OBJ_TYPE_ID = T2.OBJ_TYPE_ID";

        return $this->db->query($query)->all();
    }

    public function listObjectsTypes()
    {
        $schema = $this->db->mainSchema();
        $query =
            "SELECT
                T0.OBJ_TYPE_ID, T0.OBJ_TYPE_NAME_ES, T0.OBJ_TYPE_PARENT, T0.OBJ_TYPE_ICON,
                T1.OBJ_TYPE_ID AS OBJ_PARENT_ID, T1.OBJ_TYPE_NAME_ES AS OBJ_PARENT_NAME
            FROM {$schema}.TBL_OBJECTS_TYPES AS T0
            INNER JOIN {$schema}.TBL_OBJECTS_TYPES AS T1 ON T1.OBJ_TYPE_ID = T0.OBJ_TYPE_PARENT";
        return $this->db->query($query)->all();
    }

    public function listDataTypes()
    {
        $schema = $this->db->mainSchema();
        $query = "SELECT DTYPE_ID, DTYPE_NAME, DPARAMS_NUMBER FROM {$schema}.TBL_DATA_TYPES";
        return $this->db->query($query)->all();
    }

    public function getObjId($objName, $objType)
    {
        $schema = $this->db->mainSchema();
        $query = "SELECT OBJ_ID FROM {$schema}.TBL_SERVERS_OBJECTS WHERE OBJ_NAME = ? AND OBJ_TYPE_ID = ?";
        $result = $this->db->query($query, [$objName, $objType])->first();
        return $result["OBJ_ID"] ?? $objName;
    }

    #endregion

    #region tables

    public function tableHeaders($objId)
    {
        $schema = $this->db->mainSchema();
        $query =
            "SELECT 
                T0.OBJ_ID, T0.OBJ_NAME, T0.OBJ_TYPE_ID, T1.OBJ_TYPE_NAME, 
                T0.OBJ_SCHEMA_ID, T0.OBJ_SCHEMA_NAME, T0.OBJ_PARENT_OF
            FROM {$schema}.TBL_SERVERS_OBJECTS AS T0
            INNER JOIN {$schema}.TBL_OBJECTS_TYPES AS T1 ON T1.OBJ_TYPE_ID = T0.OBJ_TYPE_ID
            WHERE OBJ_ID = ? AND T0.OBJ_TYPE_ID = ?";
        return $this->db->query($query, [$objId, DBObjectsMap::OBJ_TABLE])->first();
    }

    public function tableColumns($objId)
    {
        $schema = $this->db->mainSchema();
        $query =
            "SELECT 
                T0.OBJ_ID, T0.OBJ_TYPE_ID, T0.OBJ_NAME, T0.OBJ_INDEX, 
                T0.OBJ_DATATYPE_ID, T1.DTYPE_NAME AS OBJ_DATATYPE_NAME, T0.OBJ_LEN_1, T0.OBJ_LEN_2,
                T0.OBJ_HAS_IDENTITY, T0.OBJ_IS_NULLABLE, T0.OBJ_DEFAULT_VALUE, 
                T0.OBJ_FK_TABLE_ID, T2.OBJ_NAME AS OBJ_FK_TABLE_NAME, T0.OBJ_FK_COLUMN_ID, T3.OBJ_NAME AS OBJ_FK_COLUMN_NAME
            FROM {$schema}.TBL_SERVERS_OBJECTS AS T0
            LEFT JOIN {$schema}.TBL_DATA_TYPES AS T1 ON T1.DTYPE_ID = T0.OBJ_DATATYPE_ID
            LEFT JOIN {$schema}.TBL_SERVERS_OBJECTS AS T2 ON T2.OBJ_ID = T0.OBJ_FK_TABLE_ID
            LEFT JOIN {$schema}.TBL_SERVERS_OBJECTS AS T3 ON T3.OBJ_ID = T0.OBJ_FK_COLUMN_ID
            WHERE T0.OBJ_PARENT_OF = ? AND T0.OBJ_TYPE_ID = ?";
        $data = $this->db->query($query, [$objId, DBObjectsMap::OBJ_TABLE_COLUMN])->all();

        return $data;
    }

    /**
     * @param string $db database obj-id
     * @param string $tableName
     * @param string $schema schema name
     * @param TableColumnModel[] $columns
     */
    public function createTable($db, $tableName, $schema, $columns)
    {
        $response = new ApiResponse();

        $this->db->beginTran();

        // ------------------------------ INSERT TABLE -----------------------
        $parameters = [
            "DB" => $db,
            "SCHEMAID" => $schema,
            "TABLENAME" => $tableName,
        ];

        $insertTableStatus = $this->db->executeProcedure(
            ScriptsDataBase::SP_INSERT_TABLE,
            $this->db->mainSchema(),
            $parameters
        );

        if (!$insertTableStatus->status()) {
            $this->db->finishTran(false);
            return $response->Error(500, $insertTableStatus->error());
        }

        $insertTableStatus = ApiResponse::map($insertTableStatus->first());
        if (!$insertTableStatus->success) {
            $this->db->finishTran(false);
            return $response->Error(500, $insertTableStatus->message);
        }

        $tableId = $insertTableStatus->data;

        // ------------------------------ INSERT TABLE COLUMNS -----------------------


        foreach ($columns as $column) {
            $parameters = [
                "TABLE_ID" => $tableId,
                "COLUMN_NAME" => $column->name,
                "DATA_TYPE_ID" => $column->dataType,
                "INDEX" => $column->index,
                "LEN1" => $column->len1,
                "LEN2" => $column->len2,
                "IS_IDENTITY" => $column->isAutoIncrement,
                "SEED" => $column->seed,
                "INCREMENT" => $column->lenIncrement,
                "IS_NULLABLE" => $column->isNullable,
                "DEFAULT_VALUE" => $column->defaultValue,
                "FK_TABLE_ID" => $column->fk_table_id,
                "FK_COLUMN_ID" => $column->fk_table_column,
            ];

            $insertColumnStatus = $this->db->executeProcedure(
                ScriptsDataBase::SP_INSERT_TABLE_COLUMN,
                $this->db->mainSchema(),
                $parameters
            );

            if (!$insertColumnStatus->status()) {
                $this->db->finishTran(false);
                return $response->Error(500, $insertColumnStatus->error());
            }

            $insertColumnStatus = ApiResponse::map($insertColumnStatus->first());
            if (!$insertColumnStatus->success) {
                $this->db->finishTran(false);
                return $response->Error(500, $insertColumnStatus->message);
            }
        }


        // ------------------------------ CREATE TABLE -----------------------


        $createTableStatus = $this->db->executeProcedure(
            ScriptsDataBase::SP_CREATE_TABLE,
            $this->db->mainSchema(),
            ["OBJ_ID" => $tableId]
        );

        if (!$createTableStatus->status()) {
            $this->db->finishTran(false);
            return $response->Error(500, $createTableStatus->error());
        }

        $createTableStatus = ApiResponse::map($createTableStatus->first());
        if (!$createTableStatus->success) {
            $this->db->finishTran(false);
            return $response->Error(500, $createTableStatus->message);
        }


        // ------------------------------ SAVE AND RETURN DATA -----------------------


        $this->db->finishTran(true);


        return $response->Ok([
            "tableInfo" => $this->tableHeaders($tableId),
            "columns" => $this->tableColumns($tableId)
        ]);
    }

    #endregion
}
