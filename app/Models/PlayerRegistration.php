<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PlayerRegistration extends Model
{
    protected $table = 'player_registration';
    
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Get the registration that owns the player.
     * @return Registration
     */
    public function registration()
    {
        return $this->belongsTo('App\Models\Registration');
    }

    /**
     * Get the parentsguardians records associated with the PlayerRegistration.
     * @return array of ParentGuardian
     */
    public function parentsguardians()
    {
        return $this->hasMany('App\Models\ParentGuardian');
    }
}
