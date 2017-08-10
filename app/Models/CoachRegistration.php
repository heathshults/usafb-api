<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CoachRegistration extends Model
{
    protected $table = 'coach_registration';
    
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Get the registration that owns the coach.
     * @return Registration
     */
    public function registration()
    {
        return $this->belongsTo('App\Models\Registration');
    }
}
