<?php

namespace App\Http\Controllers;

use App\Models\Player;

use App\Http\Services\Elasticsearch\ElasticsearchService;
use App\Http\Services\Elasticsearch\ElasticsearchPlayerQuery;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlayersController extends Controller
{

    /**
     * Search for Players
     *
     * @return string[] (json) containing the Player resources limited to 50 results per-page/request
     */
    public function search(Request $request)
    {
        $esQuery = new ElasticsearchPlayerQuery($request->query());
        if (!$esQuery->isValid()) {
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Invalid search.'
                ]
            ]);
        }
        
        $es = new ElasticsearchService();
        $es->setQuery($esQuery);
        
        $sortCriteria = $this->buildSortCriteria($request->query());
        if (!is_null($sortCriteria)) {
            $es->setSearchSort($sortCriteria['column'], $sortCriteria['order']);
        }
        
        $paginationCriteria = $this->buildPaginationCriteria($request->query());
        $es->setPaginationCriteria($paginationCriteria);
        
        $results = $es->searchPlayers();
        
        return $this->respond('OK', $results->toArray());
    }

    /**
     * Returns the Player record with the specified ID
     *
     * @return string (json) containing the Player resource OR corresponding error message
     */
    public function show($id)
    {
        $player = Player::find($id);
        if (is_null($player)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Player ('.$id.') not found.'
                ]
            ]);
        }
        return $this->respond('OK', $player);
    }
            
    /**
     * Updates the Player record with the specified ID
     *
     * @return string (json) containing the updated Player resource OR corresponding error message
     */
    public function update(Request $request, $id)
    {
        $player = Player::find($id);
        if (!isset($player)) {
            // return error
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Player ('.$id.') not found.'
                ]
            ]);
        }
        $data = $request->all();
        if (isset($data) && sizeof($data) > 0) {
            // loop through PUT fields and assign to model
            foreach ($data as $key => $value) {
                $player->setAttribute($key, $value);
            }
            if ($player->valid() && $player->save()) {
                return $this->respond('ACCEPTED', $player);
            } else {
                $errors = $player->errors();
                return $this->respond('INVALID', [
                    'error' => [
                        'message' => 'Error updated Player record.',
                        'errors' => $errors
                    ]
                ]);
            }
        }
        return $this->respond('NOT_MODIFIED', $player);
    }

    /**
     * Create a new Player record
     *
     * @return string (json) containing the new Player resource OR corresponding error message
     */
    public function create(Request $request)
    {
        $player = new Player($request->all());
        if ($player->valid() && $player->save()) {
            return $this->respond('CREATED', $player);
        } else {
            $errors = $player->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating new record.',
                    'errors' => $errors
                ]
            ]);
        }
    }
    
    /**
     * Removes the Player record and all of it's associations with the specified ID
     *
     * @return null
     */
    public function destroy($id)
    {
        $player = Player::find($id);
        if (!isset($player)) {
            // return error
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Player ('.$id.') not found.'
                ]
            ]);
        }
        $player->delete();
        return $this->respond('OK', $player);
    }
}
