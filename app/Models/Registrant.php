<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Registrant extends Model
{
    protected $table = 'registrant';
    
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Adds a create listener for model
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $usadfId = self::generateUsadfbId();
            $model->usadfb_id  = $usadfId;
        });
    }

    /**
     * Get the player record associated with the registrant.
     * @return Player
     */
    public function player()
    {
        return $this->hasOne('App\Models\Player');
    }

    /**
     * Get the coach record associated with the registrant.
     * @return Coach
     */
    public function coach()
    {
        return $this->hasOne('App\Models\Coach');
    }

    /**
     * Get the registrations records associated with the registrant.
     * @return array of Registration
     */
    public function registrations()
    {
        return $this->hasMany('App\Models\Registration');
    }

    /**
     * Will generate an id for Usadfb.
     * This is a temporary function, the generation of this id hasnt been discussed
     * This is just some ideas grabed from daily meetings
     * @return generatedId
    */
    public static function generateUsadfbId()
    {
        $maxPlayerId = DB::table('registrant')
                        ->find(DB::table('registrant')->max('id'));
        $newSequence = is_null($maxPlayerId) ? 0 : $maxPlayerId->id + 1;
        return date('Y', time()).str_pad($newSequence, 16, '0', STR_PAD_LEFT);
    }
}
