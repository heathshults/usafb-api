<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Player extends Model
{
    protected $table = 'player';
    
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
     * Get the registrant that owns the user.
     * @return Registrant
     */
    public function registrant()
    {
        return $this->belongsTo('App\Models\Registrant');
    }

    /**
     * Will generate an id for Usadfb.
     * This is a temporary function, the generation of this id hasnt been discussed
     * This is just some ideas grabed from daily meetings
     * @return generatedId
    */
    public static function generateUsadfbId()
    {
        $maxPlayerId = DB::table('player')
                        ->find(DB::table('player')->max('id'));
        $newSequence = is_null($maxPlayerId) ? 0 : $maxPlayerId->id + 1;
        return date('Y', time()).str_pad($newSequence, 16, '0', STR_PAD_LEFT);
    }
}
