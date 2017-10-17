<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ValidationTrait;

class PlayerRegistration extends Model
{
    use ValidationTrait;

    protected $table = 'player_registration';

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Get the registration that owns the player.
     * @return Registration
     */
    public function registration()
    {
        return $this->belongsTo('App\Models\Registration');
    }

    /**
     * Get the parentsguardians records associated with the PlayerRegistration.
     * @return array of ParentGuardian
     */
    public function parentsguardians()
    {
        return $this->hasMany('App\Models\ParentGuardian');
    }

    protected static function getValidationMapping()
    {
        return [
          'grade' => 'required',
          'height' => 'required',
          'graduation_year' => 'required',
          'instagram' => 'notRequired',
          'sports' => 'required',
          'twitter' => 'notRequired',
          'weight' => 'required',
          'years_at_sport' => 'required',
          'positions' => 'notRequired',
          'school_name' => 'notRequired',
          'team_age_group' => 'notRequired'
        ];
    }

    public static function insert($registrationId, $data)
    {
        $now = date('Y-m-d H:i:s');
        $data = self::validate($data);
        $data['registration_id'] = $registrationId;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        return DB::table('player_registration')->insertGetId(
            $data
        );
    }
}
