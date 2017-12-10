<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

/**
* Coach Registration collection model
*
* @package Models
*
*/

class CoachRegistration extends Eloquent
{
    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    protected $table = 'coach_registrations';
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    
    protected $fillable = [
        'id',
        "coach_id",
        'id_external',
        'id_usafb',
        'id_salesforce',
        "current",
        "level",
        "level_type",
        "roles",
        "certifications",
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
    
    public function coach()
    {
        return $this->belongsTo('App\Models\Coach');
    }
}
