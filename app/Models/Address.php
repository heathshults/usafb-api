<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

/**
* Address collection model
*
* @package Models
*
* {
*   "street_1": "1234 Main St",
*   "street_2": "Apt #1234",
*   "city": "Frisco",
*   "county": "Collin",
*   "state": "TX",
*   "postal_code": "75034",
*   "country_code": "US"
* }
*
*/

class Address extends Eloquent
{
    protected static $unguarded = true;
    protected $connection = 'mongodb';
    protected $dates = ['created_at', 'updated_at'];
    protected $fillable = [
        'street_1',
        'street_2',
        'city',
        'county',
        'state',
        'postal_code',
        'country_code'
    ];
}
