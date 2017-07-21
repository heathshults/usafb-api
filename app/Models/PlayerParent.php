<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerParent extends Model
{
    
    protected $table = 'player_parent';

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public function playerRegistration()
    {
        return $this->hasOne('App\PlayerRegistration');
    }
}
