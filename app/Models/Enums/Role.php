<?php

namespace App\Models\Enums;

class Role
{
    const SUPER_USER            = 'Super User';
    const ADMIN_USER            = 'Admin User';
    const STAKEHOLDER_USER      = 'Stakeholder User';
    const PARTNER_USER          = 'Partner User';
    const LEAGUE_CLUB_TEAM_USER = 'League/Club/Team User';
    const TEST                  = 'Automation Test';

    protected static $allRoles = [
        self::SUPER_USER,
        self::ADMIN_USER,
        self::STAKEHOLDER_USER,
        self::PARTNER_USER,
        self::LEAGUE_CLUB_TEAM_USER,
        self::TEST
    ];

    /**
     * Returns all roles
     *
     * @return array
     */
    public static function allRoles()
    {
        return static::$allRoles;
    }
}
