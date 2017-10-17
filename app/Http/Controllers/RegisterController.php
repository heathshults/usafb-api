<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';
    protected $modelService;

    /**
     * Initialize auth service
     *
     * @constructor
     */
    public function __construct()
    {
        $this->modelService = app('Model');
    }

    /**
     * Will retun a json object with s3 upload info
     * @param Illuminate\Http\Request $request with a registration json
     * @return JsonObject holding the message if the file could be uploaded to the s3 successfuly
     */
    public function player(Request $request)
    {
        $this->validate(
            $request,
            [
                'player' => 'required',
                'registrations' => 'required',
            ]
        );

        $data = $request->get('player');
        $data['registrations'] = $request->get('registrations');
        if (is_array($data['game_type'])) {
            $data['game_type'] = implode(',', $data['game_type']);
        }

        $sourceId = DB::table('source')->select('id')->where('api_key', 'USFBKey')->first()->id;
        $usafbId = $this->modelService->create(self::TYPE_PLAYER, $sourceId, $data);

        return response()->
                json(
                    ['usafb_id' => $usafbId]
                );
    }
}
