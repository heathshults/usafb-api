<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ValidationTrait;

class CoachRegistration extends Model
{
    use ValidationTrait;

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

    protected static function getValidationMapping()
    {
        return [
          'years_of_experience' => 'required',
          'certifications' => 'notRequired',
          'roles' => 'required'
        ];
    }

    public static function insert($registrationId, $data)
    {
        $now = date('Y-m-d H:i:s');
        $data = self::validate($data);
        $data['registration_id'] = $registrationId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return DB::table('coach_registration')->insertGetId(
            $data
        );
    }
}
