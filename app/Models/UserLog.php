<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

/**
 * User change log table
 *
 * @package Models
 * @author  Daylen Barban <daylen.barban@bluestarsports.com>
 */
class UserLog extends Model
{
    protected $table = 'users_logs';

    public $timestamps = false;
}
