<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ValidationTrait;

class Coach extends Model
{
    use ValidationTrait;

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

    protected static function getValidationMapping()
    {
        return [
          'years_of_experience' => 'required',
          'certifications' => 'notRequired',
          'roles' => 'required'
        ];
    }

    public static function insert($registrantId, $data)
    {
        $now = date('Y-m-d H:i:s');
        $data = self::validate($data);
        $data['registrant_id'] = $registrantId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return DB::table('coach')->insertGetId(
            $data
        );
    }
}
