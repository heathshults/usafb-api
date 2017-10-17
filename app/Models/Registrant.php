<?php
namespace App\Models;

use App\Helpers\UsafbIdHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ValidationTrait;

class Registrant extends Model
{
    use ValidationTrait;

    protected $table = 'registrant';

    protected $fillable = [];

    protected $dates = [];

    public static $rules = [];

    /**
     * Adds a create listener for model
    */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->usafb_id = UsafbIdHelper::getId($model->id);
            $model->save();
        });
    }

    /**
     * Get the player record associated with the registrant.
     * @return Player
     */
    public function player()
    {
        return $this->hasOne('App\Models\Player');
    }

    /**
     * Get the coach record associated with the registrant.
     * @return Coach
     */
    public function coach()
    {
        return $this->hasOne('App\Models\Coach');
    }

    /**
     * Get the registrations records associated with the registrant.
     * @return array of Registration
     */
    public function registrations()
    {
        return $this->hasMany('App\Models\Registration');
    }

    protected static function getValidationMapping()
    {
        return [
          'usafb_id' => 'requiredEmpty',
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
          'country' => 'required'
        ];
    }

    public static function insert($type, $data)
    {
        $now = date('Y-m-d H:i:s');
        $data = self::validate($data);
        $data['type'] = $type;
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $id = DB::table('registrant')->insertGetId(
            $data
        );
        $usafbid = UsafbIdHelper::getId($id);
        DB::table('registrant')
          ->where('id', $id)
          ->update(['usafb_id' => $usafbid]);

        return ['id' => $id, 'usafb_id' => $usafbid];
    }
}
