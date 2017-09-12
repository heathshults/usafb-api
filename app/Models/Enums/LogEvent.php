<?php

namespace App\Models\Enums;

class LogEvent
{
    const CREATE = 1;
    const UPDATE = 2;

    protected static $typeLabels = [
        self::CREATE   => 'CREATE',
        self::UPDATE   => 'UPDATE'
    ];

    /**
     * Get label value of log events
     *
     * @param int $typeValue
     * @return string
     */
    public static function label($typeValue)
    {
        return isset(static::$typeLabels[$typeValue]) ? static::$typeLabels[$typeValue] : '';
    }

    /**
     * Get array of log event id values and labels
     *
     * @return array
     */
    public static function labels()
    {
        return static::$typeLabels;
    }

    /**
     * Get array of log event values
     *
     * @return array
     */
    public static function values()
    {
        return array_keys(static::$typeLabels);
    }
}
