<?php

namespace Lib;

class ApiResponse
{
    public $code = 200;
    public $message;
    public $success;
    public $data;

    function __construct($Code = 200, $Message = "/HTTPResponse Instanciado correctamente", $Success = true, $Data = null)
    {
        $this->code = $Code;
        $this->message = self::convert_utf8($Message);
        $this->success = $Success;
        $this->data = $Data;
    }

    function Ok($data, string $Message = "Operacion completada con exito")
    {
        $this->code = 200;
        $this->message = self::convert_utf8($Message);
        $this->success = true;
        $this->data = $data;

        return $this;
    }

    function Error(int $Code = 500, $Message = "Error al realizar la operacion", $data = null)
    {
        $this->code = $Code;
        $this->message = self::convert_utf8($Message);
        $this->success = false;
        $this->data = $data;

        return $this;
    }

    function NotFound(int $Code = 404, $Message = "La direccion requerida no existe - 404 not found")
    {
        $this->code = $Code;
        $this->message = self::convert_utf8($Message);
        $this->success = false;
        $this->data = null;

        return $this;
    }

    function MethodNotAllowed($Message = "Metodo No Autorizado - 405 Method Not Allowed")
    {
        $this->code = 405;
        $this->message = self::convert_utf8($Message);
        $this->success = false;
        $this->data = null;

        return $this;
    }

    function Forbidden($Message = "Accion no autorizada - 401 Forbidden")
    {
        $this->code = 401;
        $this->message = self::convert_utf8($Message);
        $this->success = false;
        $this->data = null;

        return $this;
    }

    public static function convert_utf8($str)
    {
        return mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
    }

    /**
     * Mapea un arreglo de datos a una instancia del modelo.
     * 
     * @param array $arg
     * @return static
     */
    public static function map($arg = [])
    {
        $instance = new static();

        foreach ($arg as $key => $value) {
            $key = strtolower($key);
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        $instance->success = boolval($instance->success);

        return $instance;
    }
}
