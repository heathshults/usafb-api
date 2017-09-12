<?php

namespace App\Models\Enums;

class Role
{
    const SUPER_USER        = 1;
    const ADMIN_USER   = 2;
    const STAKEHOLDER_USER   = 3;
    const PARTNER_USER   = 4;
    const LEAGUE_CLUB_TEAM_USER   = 5;
    const TEST = 6;

    protected static $typeLabels = array(
        self::SUPER_USER        => 'Super User',
        self::ADMIN_USER   => 'Admin User',
        self::STAKEHOLDER_USER   => 'Stakeholder User',
        self::PARTNER_USER   => 'Partner User',
        self::LEAGUE_CLUB_TEAM_USER   => 'League/Club/Team User',
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
