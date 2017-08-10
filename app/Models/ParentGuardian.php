<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ParentGuardian extends Model
{
    protected $table = 'parent_guardian';
    
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Get the PlayerRegistration that owns the parent.
     * @return PlayerRegistration
     */
    public function playerRegistration()
    {
        return $this->belongsTo('App\Models\PlayerRegistration');
    }
}
