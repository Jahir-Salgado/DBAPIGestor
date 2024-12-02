<?php

namespace Lib\Database;

class MYSQL extends DBClassTemplate
{
    protected $conn;
    protected $query;
    protected $lastQueryStatus;
    protected $lastError;
    protected $systemSchema = null;
    protected $userSchema = "DBO";

    public function __construct(DBSource $conn)
    {
        $this->connect($conn);
        $this->systemSchema = $conn->db_name;
        $this->userSchema = $conn->db_name;
    }

    public function connect(DBSource $conn): void
    {
        try {
            $this->conn = @new \mysqli(
                $conn->db_server,
                $conn->db_user,
                $conn->db_pass,
                $conn->db_name,
                $conn->db_port
            );

            if ($this->conn->connect_error) {
                throw new \Exception("MYSQL CONNECTION FAILED: {$this->conn->connect_error}");
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function query($sqlQuery, $parameters = []): DBClassTemplate
    {
        $stmt = $this->conn->prepare($sqlQuery);

        if (!$stmt) {
            $this->lastQueryStatus = false;
            $this->lastError = $this->conn->error;
            return $this;
        }

        if (count($parameters) > 0) {
            $stmt->bind_param(str_repeat('s', count($parameters)), ...$parameters);
        }

        $this->lastQueryStatus = $stmt->execute();
        if (!$this->lastQueryStatus) {
            $this->lastError = $stmt->error;
        } else {
            $this->query = $stmt->get_result();
            $this->lastError = null;
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
        return $this->conn->insert_id ?? 0;
    }

    public function beginTran(): void
    {
        $this->conn->begin_transaction();
    }

    public function finishTran($status): void
    {
        if ($status) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }
    }

    public function first()
    {
        if ($this->query->num_rows > 0) {
            return DBSource::map_object_utf8($this->query->fetch_assoc());
        }
        return null;
    }

    public function all(): array
    {
        return DBSource::map_object_utf8($this->query->fetch_all(MYSQLI_ASSOC));
    }

    public function headers(): array
    {
        $headers = $this->query->fetch_fields();
        return array_map(function ($item) {
            return [
                "name" => $item->name
            ];
        }, $headers);
    }

    public function executeProcedure($procedureName, $schema = null, $procedureParameters = []): DBClassTemplate
    {
        $paramsInyected = str_repeat("?,", count($procedureParameters));
        $paramsInyected = trim($paramsInyected, ",");

        $sql = "CALL {$procedureName}({$paramsInyected})";
        $paramsValues = array_values($procedureParameters);

        return $this->query($sql, $paramsValues);
    }

    public function quoteName($string): string
    {
        return "`$string`";
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
