<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

/**
* Coach collection model
*
* @package Models
*
*/

class Coach extends Eloquent
{
    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    protected $table = 'coaches';
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'dob'];
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
        'updated_at'
    ];

    public function registrations()
    {
        return $this->hasMany('App\Models\CoachRegistration');
    }

    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }
}
