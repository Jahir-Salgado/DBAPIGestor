<?php

namespace App\Controllers;

use Lib\ApiResponse;
use App\Services\AuthServices;
use Lib\Database\DBSource;

class AuthController extends Controller
{
    protected $requireAuth = false;
    private $AuthServices;

    public function __construct()
    {
        $this->AuthServices = new AuthServices();
    }

    public function index()
    {
        return $this->validateAuthentication();
    }

    public function validateAuthentication()
    {
        return $this->AuthServices::validateSession(false);
    }

    public function login()
    {
        $response = new ApiResponse();
        $request = $this->checkRequestParams(["db_server_type", "db_server", "db_user", "db_pass", "db_name"]);

        if (!$request->success) {
            return $request;
        }

        $conn = DBSource::map($request->data);

        if (!$this->isValidServerType($conn->db_server_type)) {
            return $response->Error(500, "tipo de servidor no valido.");
        }

        $login = $this->AuthServices->loginDatabase($conn);

        if (!$login->success) {
            return $login;
        }

        $exp = 24 * 60 * 60;
        $jwt = $this->AuthServices->generateToken($conn, $exp);

        setcookie(SESSION_COOKIE_NAME, $jwt, [
            'expires' => time() + $exp,
            'path' => '/',              // Disponible en todo el dominio
            'httponly' => true,         // Solo accesible desde HTTP (no JavaScript)
            //'secure' => true,           // Solo se envía en conexiones HTTPS
            'samesite' => 'Strict'      // Evita el envío en solicitudes de terceros
        ]);

        return $response->Ok([
            "token" => $jwt,
            "duration" => $exp,
            "userSchema" => $login->data["userSchema"]
        ]);
    }

    public function logout()
    {
        return $this->AuthServices::logout();
    }
}
