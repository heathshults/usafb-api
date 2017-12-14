<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

/**
* Player Registration collection model
*
* @package Models
*
*/

class PlayerRegistration extends Eloquent
{
    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    protected $table = 'player_registrations';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    
    protected $fillable = [
        'id',
        "player_id",
        'id_external',
        'id_usafb',
        'id_salesforce',
        "current",
        "level",
        "level_type",
        "organization_name",
        "organization_state",
        "league_name",
        "season_year",
        "season",
        "school_name",
        "school_district",
        "school_state",
        "team_name",
        "team_gender",
        'created_at',
        'created_at_yyyymmdd',
        'updated_at'
    ];
        
    public function player()
    {
        return $this->belongsTo('App\Models\Player');
    }
}
