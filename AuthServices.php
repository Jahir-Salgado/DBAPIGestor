<?php

namespace App\Services;

use Lib\Database\DataBaseInstance;
use Lib\Database\DBObjectsMap;
use Lib\Database\DBSource;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Lib\ApiResponse;

class AuthServices
{
    public function loginDatabase(DBSource $conn)
    {
        $response = new ApiResponse();

        try {
            $db = DataBaseInstance::connect($conn);
            //$service = new ServerObjectsServices($conn);

            $dbData = [
                "mainSchema" => $db->mainSchema(),
                "userSchema" => $db->userSchema()
            ];
            return $response->Ok($dbData, "Conexion establecida con exito");
        } catch (\Throwable $th) {
            return $response->Error(500, $th->getMessage());
        }
    }

    public function generateToken(DBSource $loginData, $expTime)
    {
        $payload = [
            "iss" => "http://gestordbapi.test",
            "aud" => "http://gestordbapi.test",
            "iat" => time(),
            "exp" => time() + $expTime,
            "data" => $loginData
        ];

        $jwt = JWT::encode($payload, JWT_KEY, 'HS256');

        return $jwt;
    }

    public static function validateSession($returnTokenData = true)
    {
        $response = new ApiResponse();

        if (!isset($_COOKIE["JWT"])) {
            return $response->Forbidden("Token invalido o caducado.");
        }

        $token = $_COOKIE["JWT"];
        try {
            $decoded = JWT::decode($token, new Key(JWT_KEY, 'HS256'));
            $tokenData = (array) $decoded;

            $remain_time = $tokenData["exp"] - time();
            if ($remain_time <= 0) {
                return $response->Forbidden("Token invalido o caducado.");
            }

            $hours = floor($remain_time / 3600); // Dividir entre 3600 para horas
            $minutes = floor(($remain_time % 3600) / 60);

            $returnData = $returnTokenData ? $tokenData["data"] : null;
            return $response->Ok($returnData, "Sesion activa. Tiempo restante: {$hours}h {$minutes}m");
        } catch (\Exception $e) {
            return $response->Forbidden("Token invalido o caducado. {$e->getMessage()}");
        }
    }

    public static function logout()
    {
        $response = new ApiResponse();
        try {
            setcookie(SESSION_COOKIE_NAME, '', [
                'expires' => time() - 3600,  // Tiempo en el pasado
                'path' => '/',               // Debe coincidir con la configuración original
                'httponly' => true,          // Igual que al configurarla
                //'secure' => true,          // Incluye si estaba habilitado en el entorno HTTPS
                'samesite' => 'Strict'       // Igual que al configurarla
            ]);

            unset($_COOKIE[SESSION_COOKIE_NAME]);
            return $response->Ok(null, "Sesion cerrada con exito");
        } catch (\Throwable $th) {
            return $response->Ok($th->getMessage());
        }
    }
}
