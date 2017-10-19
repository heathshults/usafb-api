<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ValidationTrait;

class ParentGuardian extends Model
{
    use ValidationTrait;

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

    protected static function getValidationMapping()
    {
        return [
          'pg_mobile_phone' => 'notRequired',
          'pg_email' => 'notRequired',
          'pg_last_name' => 'notRequired',
          'pg_home_phone' => 'notRequired',
          'pg_work_phone' => 'notRequired',
        ];
    }

    public static function insert($playerRegistrationId, $dataArray)
    {
        $now = date('Y-m-d H:i:s');
        $data = [];
        foreach ($dataArray as $key => $value) {
            $validatedData = self::validate($value);
            $validatedData['player_registration_id'] = $playerRegistrationId;
            $validatedData['created_at'] = $now;
            $validatedData['updated_at'] = $now;
            $data[] = $validatedData;
        }

        return DB::table('parent_guardian')->insert(
            $data
        );
    }
}
