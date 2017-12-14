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
    protected $fillable = [
        'id_external',
        'cognito_id',
        'role_id',
        'role_permissions',
        'name_first',
        'name_last',
        'phone',
        'email'
    ];

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }
    
    protected static function boot() 
    {
        parent::boot();        
        static::saving(function($model) {
            if (is_null($model->role)) {
                return;
            }
            $model->role_permissions = $model->role->permissions;
        });
    }   
}
