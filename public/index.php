<?php
session_start();
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

require_once "../autoload.php"; // autoload de proyecto
require_once "../vendor/autoload.php"; // autoload de librerias

use lib\Route;
use lib\Database\DataBaseInstance;

require_once "../config.php";
require_once "../routes/AuthRoute.php";
require_once "../routes/ServerObjectsRoute.php";

DataBaseInstance::initialize();
Route::dispatch();
