<?php

namespace App\Models;

use ReflectionClass;

class Model
{
    /**
     * Mapea un arreglo de datos a una instancia del modelo.
     * 
     * @param array $arg
     * @return static
     */
    public static function map($arg = [], $mapEmptysInputs = true)
    {
        $instance = new static(); // Usar "static" para que apunte a la clase hija

        foreach ($arg as $key => $value) {
            if (!$mapEmptysInputs && empty($value))
                $value = null;
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }

        return $instance;
    }

    /**
     * Convierte una lista de arreglos de datos en una lista de instancias del modelo.
     * 
     * @param array $ListOfArg
     * @return static[]
     */
    public static function toList($ListOfArg = [], $mapEmptysInputs = true)
    {
        $list = [];
        if (is_array($ListOfArg)) {
            foreach ($ListOfArg as $item) {
                $list[] = static::map($item, $mapEmptysInputs);
            }
        }

        return $list;
    }

    /**
     * Devuelve las columnas (propiedades públicas) del modelo.
     * 
     * @return string[]
     */
    public static function getColumns()
    {
        $reflect = new ReflectionClass(static::class); // Usar "static" para apuntar a la clase hija
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC);

        return array_map(function ($prop) {
            return $prop->getName();
        }, $properties);
    }
}
