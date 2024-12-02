<?php

use Lib\Route;
use app\Controllers\AuthController;

Route::get("/", [AuthController::class, "index"]);
Route::get("/validateAuthentication", [AuthController::class, "validateAuthentication"]);

Route::post("/login", [AuthController::class, "login"]);
Route::post("/logout", [AuthController::class, "logout"]);
