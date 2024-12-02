<?php

namespace Lib;

use App\Controllers\Controller;

class Route
{
    private static $routes = [
        "GET" => [],
        "POST" => []
    ];

    public static function get($uri, $callback)
    {
        $uri = trim($uri, "/");
        self::$routes["GET"][$uri] = $callback;
    }

    public static function post($uri, $callback)
    {
        $uri = trim($uri, "/");
        self::$routes["POST"][$uri] = $callback;
    }

    public static function dispatch()
    {
        $uri = $_SERVER["REQUEST_URI"];
        $uri = trim($uri, "/");

        $method = $_SERVER["REQUEST_METHOD"];

        foreach (self::$routes[$method] ?? [] as $route => $callback) {

            if (strpos($route, ":") !== false) {
                $route = preg_replace("#:[a-zA-Z0-9_]+#", "([a-zA-Z0-9_]+)", $route);
            }

            if (preg_match("#^$route$#", $uri, $matches)) {
                $params = array_slice($matches, 1);

                if (is_array($callback)) {
                    [$controller, $action] = $callback;

                    /**
                     * @var Controller
                     */
                    $controller = new $controller();
                    $session = $controller->initilize();
                    if ($session->success || !$controller->isRequireAuth()) {
                        $response = $controller->$action(...$params);
                    } else {
                        $response = $session;
                    }

                } else if (is_callable($callback)) {
                    $response = $callback(...$params);
                }

                if (is_array($response) || is_object($response)) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } else {
                    header('charset=utf-8');
                    echo $response;
                }

                return;
            }
        }

        echo "404 Not Found";
    }
}
