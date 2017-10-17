<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ValidationTrait;

class Registration extends Model
{
    use ValidationTrait;

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

    protected static function getValidationMapping()
    {
        return [
          'first_name' => 'required',
          'middle_name' => 'notRequired',
          'last_name' => 'required',
          'email' => 'required',
          'gender' => 'required',
          'city' => 'required',
          'zip_code' => 'required',
          'birth_date' => 'parseToDate',
          'phone_number' => 'required',
          'game_type' => 'required',
          'level' => 'required',
          'state' => 'required',
          'address_first_line' => 'required',
          'address_second_line' => 'notRequired',
          'country' => 'required',
          'league' => 'required',
          'org_name' => 'required',
          'org_state' => 'required',
          'season' => 'required',
          'external_id' => 'notRequired',
          'right_to_market' => 'parseToBoolean',
          'team_gender' => 'notRequired',
          'team_name' => 'notRequired',
          'school_district' => 'notRequired',
          'school_state' => 'notRequired'
        ];
    }

    public static function insert($type, $sourceId, $registrantId, $data)
    {
        $now = date('Y-m-d H:i:s');
        $data = self::validate($data);
        $data['type'] = $type;
        $data['source_id'] = $sourceId;
        $data['registrant_id'] = $registrantId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return DB::table('registration')->insertGetId(
            $data
        );
    }
}
