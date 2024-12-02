<?php

namespace App\Models;

class TableColumnModel extends Model
{
    public $name = null;
    public $dataType = 1001;
    public $len1 = 255;
    public $len2 = null;
    public $index = null;
    public $isNullable = "Y";
    public $isAutoIncrement = "N";
    public $seed = 1;
    public $lenIncrement = 1;
    public $defaultValue = null;
    public $fk_table_id = null;
    public $fk_table_column = null;
}
