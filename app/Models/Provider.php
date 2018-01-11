<?php

namespace App\Models;

use App\Helpers\ApiKeyHelper;
use App\Models\RolePermissionInterface;
use EloquentFilter\Filterable;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use Log;

/**
* Provider collection model
*
* @package Models
*
*/

class Provider extends BaseModel implements RolePermissionInterface
{
    protected $connection = 'mongodb';
    protected $table = 'providers';
    protected $dates = ['created_at', 'updated_at'];
    protected $guarded = [ 'api_key' ];
    protected $attributes = [ 'active' => true ];
    
    protected $rules = [
        'name' => 'required|string',
        'contact_name_first' => 'required|string',
        'contact_name_last' => 'required|string',
        'contact_email' => 'required|email',
        'contact_phone' => 'required|string',
        'role_id' => 'required',
    ];

    protected $fillable = [
        'name',
        'contact_name_first',
        'contact_name_last',
        'contact_email',
        'contact_phone',
        'role_id',
        'role_name',
        'role_permissions',
        'api_key',
    ];
    
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
    
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
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
    
    // generate/set new token upon creation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->api_key = app('ApiKey')->generateKey();
            return true;
        });

        // if role model assigned, set role name and role permission attributes/fields
        static::saving(function ($model) {
            if (is_null($model->role)) {
                return;
            }
            $model->role_name = $model->role->name;
            $model->role_permissions = $model->role->permissions;
        });
    }
}
