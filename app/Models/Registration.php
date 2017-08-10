<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Registration extends Model
{
    protected $table = 'registration';
    
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Get the Source record associated with the registration.
     */
    public function source()
    {
        return $this->belongsTo('App\Models\Source');
    }

    /**
     * Get the PlayerRegistration record associated with the registration.
     */
    public function playerRegistration()
    {
        return $this->hasOne('App\Models\PlayerRegistration');
    }

    /**
     * Get the CoachRegistration record associated with the registration.
     */
    public function coachRegistration()
    {
        return $this->hasOne('App\Models\CoachRegistration');
    }

    /**
     * Get the registrant that owns the registration.
     * @return Registrant
     */
    public function registrant()
    {
        return $this->belongsTo('App\Models\Registrant');
    }
}
