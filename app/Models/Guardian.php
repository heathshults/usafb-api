<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
*
* Guardian collection model
*
* @package Models
*
*/
    
class Guardian extends Eloquent
{
    protected static $unguarded = true;
    protected $connection = 'mongodb';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'id',
        'name_first',
        'name_middle',
        'name_last',
        'address',
        'phone_home',
        'phone_mobile',
        'phone_work',
        'opt_in_marketing'
    ];
    
    public function address()
    {
        return $this->embedsOne('App\Models\Address');
    }
}
