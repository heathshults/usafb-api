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

class User extends Eloquent
{
    protected $connection = 'mongodb';
    protected $table = 'users';
    protected $dates = ['created_at', 'updated_at', 'deleted_at' ];
    protected $defaults = [ 'active' => true ];
    
    protected $fillable = [
        'id_external',
        'cognito_id',
        'role_id',
        'role_permissions',
        'active',
        'name_first',
        'name_last',
        'phone',
        'email',
        'address'
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
            $model->role_permissions = $model->role->permissions;
        });
    }
}
