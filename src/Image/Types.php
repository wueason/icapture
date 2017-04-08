<?php

namespace Icapture\Image;

use Icapture\Image\Types\Jpg;
use Icapture\Image\Types\Png;
use Icapture\Image\Types\Type;

/**
* Class Types
*
* @package Icapture\Image
* @author  Eason Wu <eason991@gmail.com>
*/
class Types
{
    protected static $typesMap = array(
        Jpg::FORMAT => 'Icapture\Image\Types\Jpg',
        Png::FORMAT => 'Icapture\Image\Types\Png',
    );

    /**
     * Returns all the available image types
     *
     * @return array
     */
    public static function available()
    {
        return array_keys(static::$typesMap);
    }

    /**
     * Check if an image type is available
     *
     * @param $type
     *
     * @return bool
     */
    public static function isAvailable($type)
    {
        return in_array(strtolower($type), static::available());
    }

    /**
     * Returns an instance of the requested image type
     *
     * @param string $type Image type
     *
     * @return Type
     * @throws \Exception
     */
    public static function getClass($type)
    {
        if (!static::isAvailable($type)) {
            throw new \Exception(
                "Invalid image format '{$type}'. " .
                "Allowed formats are: " . implode(', ', static::available())
            );
        }

        $className = static::$typesMap[strtolower($type)];

        return new $className();
    }
}
