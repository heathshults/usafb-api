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
     * @return string[] (json) containing the Player Registration resources limited to 50 results per-page/request
     */
    public function index(Request $request, $player_id)
    {
        $pagination = $this->buildPaginationCriteria($request->query());
        $queryFilter = $request->only('filter');
        $filters = !is_null($queryFilter['filter']) ? $queryFilter['filter'] : [];                

        $sort = $this->buildSortCriteria($request->query());
        // default sort column/order
        if (is_null($sort)) {
            $sort = [
                'column' => 'created_at', 
                'order' => 'desc'
            ];         
        }

        // validate player record
        $player = Player::find($player_id);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$id.') not found.']]);
        }

        // too bad $player->registrations()->orderBy( ... ) doesn't work (no proper query interfaces in this driver)
        $registrations = PlayerRegistration::where('player_id', $player->id)->orderBy($sort['column'], $sort['order'])->paginate();
        return $this->respond('OK', $registrations);
    }

    /**
     * Return the Player Registration record for the specified Player ID and Registration ID
     *
     * @return string (json) containing the Player Registration resource OR corresponding error message
     */
    public function show(Request $request, $player_id, $id) 
    {
        // lookup registration directly from player registration collection
        $registration = PlayerRegistration::findOne(['player_id' => $player_id, 'id' => $id]);
        if (is_null($registration)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Registration ('.$id.') not found for player ('.$player_id.').']]);          
        }
        return $this->respond('OK', $registration);        
    }
}