<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Coach extends Model
{
    protected $table = 'coach';
    
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
