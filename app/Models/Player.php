<?php

namespace App\Models;

use App\Models\Sequence;
use App\Traits\ElasticsearchTrait;
use App\Traits\UsafbRecordTrait;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use Log;

/**
* Player collection model
*
* @package Models
*
*/

class Player extends BaseModel
{
    use ElasticsearchTrait;
    use UsafbRecordTrait;

    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    
    protected $table = 'players';
    
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    // default values
    protected $attributes = [
        'opt_in_marketing' => true
    ];
    
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
    ];

    protected $rules = [
        'name_first' => 'required',
        'name_last' => 'required',
        'dob' => 'required|date',
        'gender' => 'required|in:M,F,NA',
        'email' => 'required|email',
        'phone_home' => 'required|regex:/\d{3}-\d{3}-\d{4}/',
        'phone_mobile' => 'sometimes|regex:/\d{3}-\d{3}-\d{4}/',
        'phone_work' => 'sometimes|regex:/\d{3}-\d{3}-\d{4}/',
        'social_twitter' => 'sometimes|regex:/^@.+/',
        'opt_in_marketing' => 'sometimes|boolean',
        'grade' => 'sometimes|numeric|min:1|max:12',
        'graduation_year' => 'sometimes|digits:4',
        'height_ft' => 'sometimes|numeric|min:3|max:8',
        'height_in' => 'sometimes|numeric|min:0|max:11',
        'weight' => 'sometimes|numeric|min:0|max:1000',
        'years_experience' => 'sometimes|numeric|min:0|max:50',
        'sports.*' => 'sometimes|in:basketball,baseball,soccer,lacrosse,'.
            'swimming,volleyball,softball,hockey,tennis,golf,rugby,other',
        'address' => 'required',
        'address.street_1' => 'required',
        'address.city' => 'required',
        'address.state' => 'required|alpha|size:2',
        'address.postal_code' => 'required|regex:/\d{5}/',
        'address.county' => 'required',
        'address.country' => 'sometimes|alpha|size:2',
        'guardians.*' => 'sometimes',
        'guardians.*.name_first' => 'required',
        'guardians.*.name_last' => 'required',
        'guardians.*.address.street_1' => 'required',
        'guardians.*.address.city' => 'required',
        'guardians.*.address.state' => 'required|alpha|size:2',
        'guardians.*.address.country' => 'sometimes|alpha|size:2',
        'guardians.*.address.postal_code' => 'required|regex:/\d{5}/',
    ];

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
    
    // trait method implementation
    public function searchContent()
    {
        $body = [
            'id' => $this->id,
            'id_external' => $this->id_external,
            'id_usafb' => $this->id_usafb,
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
            'level' => null,
            'level_type' => null,
            'position' => null,
            'created_date' => $this->created_date,
            'updated_date' => $this->updated_date,
        ];
        
        // set registration specific fields if Player has current reg
        $currentReg = $this->registrations()->where('current', true)->first();
        if (!is_null($currentReg)) {
            $body['level'] = $currentReg->level;
            $body['level_type'] = $currentReg->level_type;
            $body['position'] = $currentReg->position;
        }
        
        return $body;
    }
}
