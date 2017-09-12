<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Source extends Model
{
    protected $table = 'source';
    
    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Get the registrations records associated with the registrant.
     * @return array of Registration
     */
    public function registrations()
    {
        return $this->hasMany('App\Models\Registration');
    }
}
