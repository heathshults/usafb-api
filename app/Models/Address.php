<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
* Address collection model
*
* @package Models
*
*/

class Address extends Eloquent
{
    protected static $unguarded = true;
    protected $connection = 'mongodb';
    protected $dates = ['created_at', 'updated_at'];
    protected $attributes = [ 'country' => 'US' ];
    protected $fillable = [
        'street_1',
        'street_2',
        'city',
        'county',
        'state',
        'postal_code',
        'country'
    ];
}
