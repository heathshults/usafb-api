<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

/**
* Player collection model
*
* @package Models
*
*/

class Player extends Eloquent
{
    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    protected $table = 'players';
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'dob'];
    protected $fillable = [
        'id_external',
        'name_first',
        'name_middle',
        'name_last',
        'dob',
        'grade',
        'graduation_year',
        'gender',
        'height_ft',
        'height_in',
        'weight',
        'email',
        'phone_home',
        'phone_mobile',
        'phone_work',
        'social_twitter',
        'social_instagram',
        'opt_in_marketing',
        'address',
        'sports',
        'years_experience',
        'guardians'
    ];

    public function registrations()
    {
        return $this->hasMany('App\Models\PlayerRegistration');
    }

    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }

    public function guardians()
    {
        return $this->embedsMany('App\Models\Guardian');
    }
}
