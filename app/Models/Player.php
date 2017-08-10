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
     * Get the registrant that owns the user.
     * @return Registrant
     */
    public function registrant()
    {
        return $this->belongsTo('App\Models\Registrant');
    }
}
