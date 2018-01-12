<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Coach;
use App\Models\Player;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * StatsController
 * Application statistics/counts/aggregates
 *
 * @package    Http
 * @subpackage Controllers
 */
class StatsController extends Controller
{

    /**
     * Get system stats overview
     * Url: GET /stats/overview
     *
     * @param Request $request
     *
     * @return json
     */
    public function overview(Request $request)
    { 
        // get total # of players in system
        $numPlayers = Player::count();
        
        // get total # of coaches in system
        $numCoaches = Coach::count();
        
        $results = [
            "num_players" => $numPlayers,
            "num_coaches" => $numCoaches,
            "players" => [
                "levels" => $this->getPlayerLevelTypes()
            ],
            "coaches" => [
                "levels" => $this->getCoachLevelTypes()
            ],
        ];
        
        return $this->respond('OK', $results);        
    }
    
    /**
     * Get Player Registration level types counts
     *
     * @return array results
     */
    protected function getPlayerLevelTypes() : array {
        return $this->getLevelTypes(Player::class);
    }

    /**
     * Get Coach Registration level types counts
     *
     * @return array results
     */    
    protected function getCoachLevelTypes() : array {
        return $this->getLevelTypes(Coach::class);
    }
    
    /**
     * Get level types for the provided Player or Coach model Class
     *
     * @param class $model
     *
     * @return results[]
     */    
    private function getLevelTypes($model) : array 
    {
        $results = [];
        
        $levelTypes = $model::raw( function ($collection) {
                    return $collection->aggregate([
                        [ '$unwind' => '$registrations' ],
                        [ '$match' => [ 'registrations.current' => true ] ],
                        [
                            '$group' => [
                                '_id' => '$registrations.level_type',
                                'count' => [ '$sum' => 1 ]
                            ],
                        ]
                    ]);
                });        

        foreach($levelTypes->all() as $levelType) {
            $level = $levelType->getAttribute('_id');
            $count = $levelType->getAttribute('count');
            $results[] = [
                'name' => $level,
                'num' => $count
            ];
        }
        
        return $results;
    }
    
}
