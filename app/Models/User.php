<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

use App\Models\Role;

/**
* User collection model
*
* @package Models
*
*/

class User extends BaseModel
{
    protected $connection = 'mongodb';
    
    protected $table = 'users';
    
    protected $guarded = [ 'email', 'role_id' ];
    
    protected $dates = [ 'created_at', 'updated_at', 'last_login_at' ];
    
    protected $attributes = [ 'active' => true ];
    
    protected $rules = [
        'active' => 'required|boolean',
        'role_id' => 'required',
        'name_first' => 'required',
        'name_last' => 'required',
        'email' => 'required|email',
        'address' => 'required',
        'address.street_1' => 'required',
        'address.city' => 'required',
        'address.state' => 'required|size:2',
        'address.postal_code' => 'required',
        'address.country' => 'sometimes|size:2'
    ];
    
    protected $fillable = [
        'id_external',
        'id_cognito',
        'active',
        'role_id',
        'role_name',
        'role_permissions',
        'organization_name',
        'name_first',
        'name_last',
        'phone',
        'email',
        'address',
        'last_login_at'
    ];

    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }
    
    public function deactivate()
    {
        $this->active = false;
        $this->save();
        return true;
    }

    public function activate()
    {
        $this->active = true;
        $this->save();
        return true;
    }
    
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (is_null($model->role)) {
                return;
            }
            $model->role_name = $model->role->name;
            $model->role_permissions = $model->role->permissions;
        });
    }
}
