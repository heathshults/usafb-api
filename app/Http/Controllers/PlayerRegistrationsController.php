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
     * @param Request $request
     * @param string $playerId
     *
     * @return string (json) containing the Player Registration resources limited to 50 results per-page/request
     */
    public function index(Request $request, $playerId)
    {
        // find Player record (or error)
        $player = Player::find($playerId);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$playerId.') not found.']]);
        }
        
        $sort = $this->buildSortCriteria($request->query(), [ 'column' => 'created_at', 'order' => 'desc' ]);
        
        // find Player Registration records ordered by *sort*
        $registrations = $player->registrations()->sortBy($sort['column']);
        
        return $this->respond('OK', $registrations);
    }

    /**
     * Return the Player Registration record for the specified Player ID and Registration ID
     *
     * @param Request $request
     * @param string $id
     * @param string $playerId
     *
     * @return string (json) containing the Player Registration resource OR corresponding error message
     */
    public function show(Request $request, $id, $playerId)
    {
        // find Player record (or error)
        $player = Player::find($playerId);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$playerId.') not found.']]);
        }

        // find Player Registration record with ID (or error)
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
     * @param Request $request
     * @param string $id
     *
     * @return string (json) containing new record resource OR corresponding error message
     */
    public function create(Request $request, $id)
    {
        // find Player record (or error)
        $player = Player::find($id);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$playerId.') not found.']]);
        }
        
        // instantiate new Player Registraiton and validate
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

        // set all past registrations current to false - change later to use indexed find/update
        foreach ($player->registrations()->where('current', true)->all() as $registration) {
            $registration->current = false;
            $registration->save();
        }
        
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
    
    /**
     * Update player registration record
     *
     * @param Request $request
     * @param string $id
     * @param string $playerId
     *
     * @return string (json) containing new record resource OR corresponding error message
     */
    public function update(Request $request, $id, $playerId)
    {
        // find Player record (or error)
        $player = Player::find($playerId);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Player ('.$playerId.') not found.'
                ]
            ]);
        }
        
        // find Registration record (or error)
        $registration = $player->registrations()->find($id);
        if (is_null($registration)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Registration ('.$id.') not found for Player ('.$playerId.').'
                ]
            ]);
        }
        
        $requestJson = $request->json()->all();
        
        // merge/fill registration object with json values
        $registration->fill($requestJson);
        
        // validate merged record and save (or error)
        if ($registration->valid() && $registration->save()) {
            return $this->respond('OK', $registration);
        } else {
            $errors = $registration->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error updating Registration record.',
                    'errors' => $errors
                ]
            ]);
        }
    }
}
