<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\PlayerRegistration;

use League\fractalService\Pagination\IlluminatePaginatorAdapter;
use League\fractalService\Manager;
use League\fractalService\Resource\Collection;
use League\fractalService\Resource\Item;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlayerRegistrationsController extends Controller
{

    /**
     * Returns the player registration records
     *
     * @return string (json) containing the Player Registration resources limited to 50 results per-page/request
     */
    public function index(Request $request, $playerId)
    {
        $player = Player::find($playerId);
        // validate player record
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$playerId.') not found.']]);
        }
        
        $sort = $this->buildSortCriteria($request->query(), [ 'column' => 'created_at', 'order' => 'desc' ]);
        $registrations = $player->registrations()->sortBy($sort['column']);
        
        return $this->respond('OK', $registrations);
    }

    /**
     * Return the Player Registration record for the specified Player ID and Registration ID
     *
     * @return string (json) containing the Player Registration resource OR corresponding error message
     */
    public function show(Request $request, $id, $playerId)
    {
        // validate player record
        $player = Player::find($playerId);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$playerId.') not found.']]);
        }

        // lookup registration directly from player registration collection
        $registration = $player->registrations()->find($id);
        if (is_null($registration)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Registration ('.$id.') not found for player ('.$playerId.').'
                ]
            ]);
        }
        
        return $this->respond('OK', $registration);
    }
    
    /**
     * Create a new player registration record
     *
     * @return string (json) containing new record resource OR corresponding error message
     */
    public function create(Request $request, $id)
    {
        $player = Player::find($id);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$playerId.') not found.']]);
        }
        
        // instantiate new player registraiton and validate fields
        $playerRegistration = new PlayerRegistration($request->all());
        if (!$playerRegistration->valid()) {
            $errors = $playerRegistration->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating new player registration record.',
                    'errors' => $errors
                ]
            ]);
        }

        // TODO set all registrations to current = false
        
        // associate new embedded registration with player record
        $player->registrations()->associate($playerRegistration);

        // do final player validation (with combined data) and save
        if ($player->valid() && $player->save()) {
            return $this->respond('ACCEPTED', $playerRegistration);
        } else {
            $errors = $player->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating new player registration record.',
                    'errors' => $errors
                ]
            ]);
        }
    }
}
