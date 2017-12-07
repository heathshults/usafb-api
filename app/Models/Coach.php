<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Jenssegers\Mongodb\Eloquent\Model;

class Coach extends Model
{
    use SoftDeletes;

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public $rules = [];

    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }
}
