<?php

namespace Lib\Database;

abstract class DBClassTemplate implements DBInterface
{
    protected $conn;
    protected $connStatus;
    protected $query;
    protected $lastQueryStatus;
    protected $lastError;
    protected $systemSchema;
    protected $userSchema;

    abstract public function connect(DBSource $conn): void;
    abstract public function query($sqlQuery, $parameters = []): DBClassTemplate;
    abstract public function status(): bool;
    abstract public function error(): string;
    abstract public function lastInsertId(): int;
    abstract public function beginTran(): void;
    abstract public function finishTran($status): void;
    abstract public function first();
    abstract public function all(): array;
    abstract public function headers(): array;
    abstract public function executeProcedure($procedureName, $schema = null, $procedureParameters = []): DBClassTemplate;
    abstract public function quoteName($string): string;
    abstract public function mainSchema();
    abstract public function userSchema();
}
