<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerTeam extends Model
{
    
    protected $table = 'player_team';

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [
        // Validation rules
    ];

    public function player()
    {
        return $this->hasOne('App\Player');
    }
}
