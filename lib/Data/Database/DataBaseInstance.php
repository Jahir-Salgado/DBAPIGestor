<?php

namespace Lib\Database;

use Lib\Database\DBClassTemplate;

class DataBaseInstance
{

    public static $MSSQL_SERVER_1_SRC;
    public static $MYSQL_SERVER_1_SRC;

    public static function initialize()
    {
        self::$MSSQL_SERVER_1_SRC = new DBSource(
            DBSource::MSSQL_SERVER,
            "SQL1001.site4now.net",
            "db_aaed69_mssqldb_admin",
            "Empanadas31.",
            "db_aaed69_mssqldb"
        );

        self::$MYSQL_SERVER_1_SRC = new DBSource(
            DBSource::MYSQL_SERVER,
            "MYSQL1001.site4now.net",
            "aaed69_mysqldb",
            "Empanadas31.",
            "db_aaed69_mysqldb"
        );
    }

    public static function connect(DBSource $conn): DBClassTemplate
    {
        $db = null;
        if ($conn->db_server_type == DBSource::MSSQL_SERVER) {
            $db = new MSSQL($conn);
        } else if ($conn->db_server_type == DBSource::MYSQL_SERVER) {
            $db = new MYSQL($conn);
        }

        return $db;
    }
}
