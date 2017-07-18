<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{

    protected $table = 'player';


    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public function playerTeam()
    {
        return $this->belongsTo('App\PlayerTeam');
    }


    // Relationships
}
