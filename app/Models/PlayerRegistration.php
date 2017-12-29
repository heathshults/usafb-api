<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;

use Log;
use Validator;

/**
* Player Registration collection model embedded within Player
*
* @package Models
*
*/

class PlayerRegistration extends BaseModel
{
    protected $dates = ['created_at', 'updated_at' ];
    
    protected $fillable = [
        'id_external',
        'current',
        'level',
        'level_type',
        'position',
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
    
    protected $rules = [
        'current' => 'required|boolean',
        'level' => 'required|in:youth,middle_school,freshman,jv,varsity,college,professional',
        'level_type' => 'required|in:youth_flag,7on7,rookie_tackle,11_player_tackle,adult_flag,other',
        'position' => 'required|in:head_coach,quaterback_coach,wide_receiver_coach,linebacker_coach,
        offensive_coordinator,special_teams,assistant_coach,tight_end_coach,running_back_coach,
        defensive_back_coach,defensive_cooridnator',
        'organization_name' => 'required',
        'organization_state' => 'required|size:2',
        'league_name' => 'required',
        'season' => 'required|in:fall,spring,summer,winter',
        'season_year' => 'required|numeric',
        'team_gender' => 'sometimes|in:M,F,NA',
        'school_state' => 'sometimes|size:2'
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
