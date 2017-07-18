<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerRegistration extends Model
{
    protected $table = 'player_registration';
    
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public function gameType()
    {
        return $this->hasOne('App\GameType');
    }

    public function playerLevel()
    {
        return $this->hasOne('App\PlayerLevel');
    }
    
    public function player()
    {
        return $this->hasOne('App\Player');
    }
}
