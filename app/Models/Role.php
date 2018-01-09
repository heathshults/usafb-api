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
        'add_player',
        'add_coach',
        'update_coach',
        'update_player',
        'delete_coach',
        'delete_player',
    ];
        
    protected $connection = 'mongodb';

    protected $table = 'roles';

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = [ 'name', 'permissions' ];

    protected $rules = [
        'name' => 'required|unique:roles',
        'permissions.*' => 'required|in:export_players,import_players,import_coaches,export_coaches,
        manage_users,view_dashboard,view_players,view_coaches,add_player,add_coach,update_coach,
        update_player,delete_coach,delete_player'
    ];
                
    // synchronize role permissions in user model on save/changes
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (!$model->isDirty()) {
                return;
            }
            
            // update all provider embedded role values for this role
            Provider::where([ 'role_id' => $model->id ])->update(
                [
                    'role_name' => $model->name,
                    'role_permissions' => $model->permissions
                ],
                [
                    'upsert' => true
                ]
            );
            
            // update all user embedded role values for this role
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
