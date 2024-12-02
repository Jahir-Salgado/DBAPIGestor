<?php

namespace Lib\Database;

interface DBInterface
{
    public function connect(DBSource $conn);
    public function query($sqlQuery, $parameters = []);
    public function status();
    public function error();
    public function lastInsertId();
    public function beginTran();
    public function finishTran($status);
    public function first();
    public function all();
    public function headers();
    public function executeProcedure($procedureName, $procedureParameters = []);
    public function quoteName($string);
}