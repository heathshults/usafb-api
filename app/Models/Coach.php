<?php

namespace App\Models;

use App\Traits\ElasticsearchTrait;
use App\Traits\UsafbRecordTrait;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use Log;

/**
* Coach collection model
*
* @package Models
*
*/

class Coach extends BaseModel
{
    use ElasticsearchTrait;
    use UsafbRecordTrait;
    
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
        'organization_name',
        'organization_state',
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

    public function registrations()
    {
        return $this->embedsMany('App\Models\CoachRegistration');
    }

    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }
    
    public function searchContent()
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
            'level' => null,
            'level_type' => null,
            'position' => null
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
