<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

/**
* Player Registration collection model embedded within Player
*
* @package Models
*
*/

class PlayerRegistration extends Eloquent
{
    protected $dates = ['created_at', 'updated_at' ];
    
    protected $fillable = [
        'id_external',
        'current',
        'level',
        'level_type',
        'organization_name',
        'organization_state',
        'league_name',
        'season_year',
        'season',
        'school_name',
        'school_district',
        'school_state',
        'team_name',
        'team_gender',
        'created_at',
        'created_at_yyyymmdd',
        'updated_at'
    ];
    
    protected $attributes = [
        'current' => true
    ];
    
    public function __construct($attributes = [])
    {
        if (!$this->exists) {
            $this->attributes['created_at_yyyymmdd'] = Date('Y-m-d');
        }
        parent::__construct($attributes);
    }
    
    public function player()
    {
        return $this->belongsTo('Player');
    }
}
