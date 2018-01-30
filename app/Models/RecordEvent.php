<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
* Record Event collection model
*
* @package Models
*
*/

class RecordEvent extends BaseModel
{
    protected $table = 'record_events';
    protected $connection = 'mongodb';
    protected $dates = [ 'created_at' ];
    protected $attributes = [ 'deleted' => false ];
    protected $fillable = [ 'record_type', 'record_id', 'deleted' ];
}
