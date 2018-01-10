<?php

namespace App\Models;

use App\Models\Role;
use App\Models\RolePermissionInterface;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use Log;

/**
* User collection model
*
* @package Models
*
*/

class User extends BaseModel implements RolePermissionInterface
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
    
    // return boolean result for role having specified permission
    public function hasRolePermission(string $permission) : bool
    {
        if (!is_null($permission) && !is_null($this->role_permissions)) {
            return in_array($permission, $this->role_permissions);
        }
        return false;
    }

    // return boolean result for role having specified permission
    public function hasRolePermissions(array $permissions) : bool
    {
        if (is_null($permissions)) {
            return true;
        }
        if (is_null($this->role_permissions)) {
            return false;
        }
        foreach ($permissions as $permission) {
            if (!in_array($permission, $this->role_permissions)) {
                return false;
            }
        }
        return true;
    }
    
    // if role model assigned, set role name and role permission attributes/fields
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
