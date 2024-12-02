<?php

use Lib\Route;
use App\Controllers\ServerObjectController;
use App\Controllers\TableController;

Route::get("/serverObjects/list", [ServerObjectController::class, "list"]);
Route::get("/serverObjects/types", [ServerObjectController::class, "listObjTypes"]);
Route::get("/serverObjects/dataTypes", [ServerObjectController::class, "listDataTypes"]);
Route::get("/serverObjects/getObjId/:objTypeId/:objName", callback: [ServerObjectController::class, "getObjId"]);

Route::post("/table/:db/create", [TableController::class, "create"]);
Route::get("/table/:objId/details", [TableController::class, "details"]);
