<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;

use EloquentFilter\Filterable;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

use Log;

/**
* Role collection model
*
* @package Models
*
*/

class Role extends BaseModel
{
    const PERMISSIONS = [
        'export_players',
        'import_players',
        'import_coaches',
        'export_coaches',
        'manage_users',
        'view_dashboard',
        'view_players',
        'view_coaches',
    ];
        
    protected $connection = 'mongodb';

    protected $table = 'roles';

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = [ 'name', 'permissions' ];

    protected $rules = [
        'name' => 'required|unique:roles',
        'permissions.*' => 'required|in:export_players,import_players,import_coaches,export_coaches,
        manage_users,view_dashboard,view_players,view_coaches'
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
                [
                    'role_name' => $model->name,
                    'role_permissions' => $model->permissions
                ],
                [
                    'upsert' => true
                ]
            );
        });
    }
}
