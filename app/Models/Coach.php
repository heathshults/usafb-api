<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

use App\Traits\ElasticsearchTrait;

/**
* Coach collection model
*
* @package Models
*
*/

class Coach extends BaseModel
{
    use ElasticsearchTrait;
    
    protected $connection = 'mongodb';
    
    protected $table = 'coaches';
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    protected $fillable = [
        'id',
        'id_external',
        'id_usafb',
        'id_salesforce',
        'name_first',
        'name_middle',
        'name_last',
        'dob',
        'gender',
        'email',
        'phone_home',
        'phone_mobile',
        'phone_work',
        'opt_in_marketing',
        'address',
        'years_experience',
        'level',
        'level_type',
        'positions',
        'organization_name',
        'organization_state',
        'created_at',
        'created_at_yyyymmdd',
        'updated_at_yyyymmdd',
        'updated_at'
    ];
    
    protected $rules = [
        'name_first' => 'required',
        'name_last' => 'required',
        'dob' => 'required|date',
        'gender' => 'required|in:M,F,NA',
        'email' => 'required|email',
        'phone_home' => 'required|regex:/\d{3}-\d{3}-\d{4}/',
        'opt_in_marketing' => 'sometimes|boolean',
        'years_experience' => 'required|numeric|min:0|max:50',
        'level' => 'required|in:youth,middle_school,freshman,jv,varsity,college,professional',
        'level_type' => 'required|in:youth_flag,7on7,rookie_tackle,11_player_tackle,adult_flag,other',
        'positions.*' => 'sometimes|in:head_coach,quaterback_coach,wide_receiver_coach,linebacker_coach,
        offensive_coordinator,special_teams,assistant_coach,tight_end_coach,
        running_back_coach, defensive_back_coach,defensive_cooridnator',
        'organization_name' => 'required',
        'organization_state' => 'required|size:2',
        'address' => 'required',
        'address.street_1' => 'required',
        'address.city' => 'required',
        'address.state' => 'required|alpha|size:2',
        'address.postal_code' => 'required|regex:/\d{5}/',
        'address.county' => 'required',
        'address.country' => 'sometimes|alpha|size:2'
    ];

    public function __construct($attributes = [])
    {
        if (!$this->exists) {
            $this->attributes['created_at_yyyymmdd'] = Date('Y-m-d');
        }
        parent::__construct($attributes);
    }

    public function registrations()
    {
        return $this->embedsMany('App\Models\CoachRegistration');
    }

    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }
    
    public function toSearchBody()
    {
        $body = [
            'id' => $this->id,
            'id_external' => $this->id_external,
            'id_usafb' => $this->id_usafb,
            'created_date' => $this->created_at_yyyymmdd,
            'updated_date' => $this->updated_at_yyyymmdd,
            'name_first' => $this->name_first,
            'name_middle' => $this->name_middle,
            'name_last' => $this->name_last,
            'dob' => $this->dob,
            'phone_home' => $this->phone_home,
            'gender' => $this->gender,
            'city' => $this->address->city,
            'state' => $this->address->state,
            'county' => $this->address->county,
            'postal_code' => $this->address->postal_code,
            'level' => $this->level,
            'level_type' => $this->level_type
        ];
        return $body;
    }
    
    public static function boot()
    {
        parent::boot();
        self::updating(function ($model) {
            $model->updated_at_yyyymmdd = Date('Y-m-d');
        });
    }
}
