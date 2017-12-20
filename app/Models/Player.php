<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

use App\Traits\ElasticsearchTrait;

/**
* Player collection model
*
* @package Models
*
*/

class Player extends Eloquent
{
    use ElasticsearchTrait;

    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    protected $table = 'players';
    protected $dates = [ 'created_at', 'updated_at', 'deleted_at' ];
    protected $fillable = [
        'id_external',
        'id_usafb',
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
        'guardians',
        'created_at_yyyymmdd',
        'updated_at_yyyymmdd'
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
        return $this->embedsMany('App\Models\PlayerRegistration');
    }
        
    public function currentRegistration()
    {
        return $this->registrations()->last();
    }

    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }

    public function guardians()
    {
        return $this->embedsMany('App\Models\Guardian');
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
            'postal_code' => $this->address->postal_code
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
