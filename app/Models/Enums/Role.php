<?php

namespace App\Models\Enums;

class Role
{
    const USAFB_ADMIN        = 1;
    const TEST = 3;

    protected static $typeLabels = array(
        self::USAFB_ADMIN        => 'U.S. Football Staff',
        self::TEST   => 'Automation Test'
    );

    /**
     * @param int $typeValue
     * @return string
     */
    public static function label($typeValue)
    {
        return isset(static::$typeLabels[$typeValue]) ? static::$typeLabels[$typeValue] : '';
    }

    /**
     * @return array
     */
    public static function labels()
    {
        return static::$typeLabels;
    }

    /**
     * @return array
     */
    public static function values()
    {
        return array_keys(static::$typeLabels);
    }
}
