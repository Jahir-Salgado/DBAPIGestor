<?php

namespace Lib\Database;

class MSSQL extends DBClassTemplate
{
    protected $conn;
    protected $query;
    protected $lastQueryStatus;
    protected $lastError;
    protected $systemSchema = "_SYS";
    protected $userSchema = "DBO";

    public function __construct(DBSource $conn)
    {
        $this->connect($conn);
    }

    public function connect(DBSource $conn): void
    {
        try {
            $connectionOptions = [
                "Database" => $conn->db_name,
                "UID" => $conn->db_user,
                "PWD" => $conn->db_pass,
                "CharacterSet" => "UTF-8"
            ];

            $this->conn = sqlsrv_connect($conn->db_server, $connectionOptions);

            if (!$this->conn) {
                throw new \Exception("MSSQL CONNECTION FAILED: " . sqlsrv_errors()[0]["message"]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function query($sqlQuery, $parameters = []): DBClassTemplate
    {
        // Preparar la consulta
        $stmt = sqlsrv_prepare($this->conn, $sqlQuery, $parameters);

        if (!$stmt) {
            $this->lastQueryStatus = false;
            $this->lastError = sqlsrv_errors();
            return $this;
        }

        // Ejecutar la consulta
        $this->lastQueryStatus = sqlsrv_execute($stmt);

        if (!$this->lastQueryStatus) {
            $this->lastError = sqlsrv_errors()[0]["message"];
            $this->query = null;
        } else {
            $this->lastError = null;
            $this->query = $stmt;
        }

        return $this;
    }

    public function status(): bool
    {
        return $this->lastQueryStatus;
    }

    public function error(): string
    {
        return $this->lastError;
    }

    public function lastInsertId(): int
    {
        $stmt = sqlsrv_query($this->conn, "SELECT @@IDENTITY AS last_id");
        if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            return $row["last_id"];
        } else {
            return 0;
        }
    }

    public function beginTran(): void
    {
        sqlsrv_begin_transaction($this->conn);
    }

    public function finishTran($status): void
    {
        if ($status) {
            sqlsrv_commit($this->conn);
        } else {
            sqlsrv_rollback($this->conn);
        }
    }

    public function first()
    {
        if ($this->query) {
            $row = sqlsrv_fetch_array($this->query, SQLSRV_FETCH_ASSOC);
            return DBSource::map_object_utf8($row);
        }
        return null;
    }

    public function all(): array
    {
        $results = [];
        if ($this->query) {
            while ($row = sqlsrv_fetch_array($this->query, SQLSRV_FETCH_ASSOC)) {
                $results[] = DBSource::map_object_utf8($row);
            }
        }
        return $results;
    }

    public function headers(): array
    {
        $headers = sqlsrv_field_metadata($this->query);
        return array_map(function ($item) {
            return [
                "name" => $item["Name"]
            ];
        }, $headers);
    }

    public function executeProcedure($procedureName, $schema = null, $procedureParameters = []): DBClassTemplate
    {
        $paramsNames = array_map(function ($item) {
            return "@{$item} = ?";
        }, array_keys($procedureParameters));

        $schema = $this->quoteName($schema ?? "DBO");
        $procedureName = $this->quoteName($procedureName);

        $sql = "EXEC {$schema}.{$procedureName} ";
        $sql .= implode(", ", $paramsNames);
        $paramsValues = array_values($procedureParameters);

        return $this->query($sql, $paramsValues);
    }

    public function quoteName($string): string
    {
        return "[$string]";
    }

    public function mainSchema()
    {
        return $this->systemSchema;
    }

    public function userSchema()
    {
        return $this->userSchema;
    }
}
