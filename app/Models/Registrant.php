<?php
namespace App\Models;

use App\Helpers\UsafbIdHelper;
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

        static::created(function ($model) {
            $model->usafb_id = UsafbIdHelper::getId($model->id);
            $model->save();
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
}
