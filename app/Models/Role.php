<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Log;

/**
* Role collection model
*
* @package Models
*
*/

class Role extends Eloquent
{
    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    protected $table = 'roles';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'name',
        'permissions'
    ];

    // synchronize role permissions in user model on save/changes
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (!$model->isDirty()) {
                return;
            }
            User::where([ 'role_id' => $model->id ])->update(
                [ 'role_permissions' => $model->permissions ],
                [ 'upsert' => true ]
            );
        });
    }
}
