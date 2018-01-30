<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Log;

/**
 * id_sequences collection
 *
 * @package Models
 */
class IdSequence extends Eloquent
{
    protected $connection = 'mongodb';
    protected $table = 'id_sequences';
    protected $fillable = [
        'year',
        'month',
        'index',
        'prime',
        'inverse',
        'random'
    ];
    public $timestamps = false;
}
