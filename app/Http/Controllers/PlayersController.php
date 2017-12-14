<?php

namespace App\Http\Controllers;

use App\Models\Player;

use League\fractalService\Pagination\IlluminatePaginatorAdapter;
use League\fractalService\Manager;
use League\fractalService\Resource\Collection;
use League\fractalService\Resource\Item;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlayersController extends Controller
{

    /**
     * Returns the player records
     *
     * @return string[] (json) containing the Player resources limited to 50 results per-page/request
     */
    public function index(Request $request)
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

        $players = Player::orderBy($sort['column'], $sort['order'])->paginate(50);
        return response()->json($players);
    }

    /**
     * Returns the player record with the specified ID
     *
     * @return string (json) containing the Player resource OR corresponding error message
     */
    public function show($id)
    {
        $player = Player::find($id);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$id.') not found.']]);
        }
        return $this->respond('OK', $player);
    }
    
    public function search()
    {
        // TODO implement Elasticsearch
        return response()->json(array('OK' => 'ok'));
    }
        
    /**
     * Updates the player record with the specified ID
     *
     * @return string (json) containing the updated Player resource OR corresponding error message
     */
    public function update(Request $request, $id)
    {
        $player = Player::find($id);
        if (!isset($player)) {
            // return error
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$id.') not found.']]);
        }
        $data = $request->all();
        if (isset($data) && sizeof($data) > 0) {
            // TODO validate
            if ($player->update($data)) {
                return $this->respond('ACCEPTED', $player);
            }
        }
        return $this->respond('NOT_MODIFIED', $player);
    }
    
    /**
     * Removes the player record and all of it's associations with the specified ID
     *
     * @return null
     */
    public function destroy($id)
    {
        $player = Player::find($id);
        if (!isset($player)) {
            // return error
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$id.') not found.']]);
        }
        $player->delete();
        return $this->respond('OK', $player);
    }
}
