<?php

namespace App\Console\Commands\Warehouse;

use App\Models\RecordEvent;
use App\Models\Player;
use App\Models\Coach;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;

class PlayerStager implements StagerInterface
{
    protected $batchSize = 0;
    
    /**
     * Constructor
     *
     * @param int size of batches to process in
     */
    public function __construct(int $batchSize = 100)
    {
        $this->batchSize = $batchSize;
    }
        
    /**
     * Get number of events ready to be staged
     *
     * @return int number of events to stage
     */
    public function numEvents() : int
    {
        return RecordEvent::where('record_type', 'players')
            ->where('deleted', false)
            ->count();
    }
    
    /**
     * Stage pending Coach event record removals to Datawarehouse staging tables
     *
     * @return int number of results staged for removal
     */
    public function stageRemovals(array $options = []) : int
    {
        $numRemoved = 0;
        return $numRemoved;
    }

    /**
     * Stage pending Player event records to Datawarehouse staging tables
     *
     * @return int number of results staged
     */
    public function stageUpdates(array $options = []) : int
    {
        $numStaged = 0;
                
        // find non-delete player event records
        $playerEvents = RecordEvent::where('record_type', 'players')
            ->where('deleted', false)
            ->get(['record_id', 'deleted'])
            ->toArray();

        // if no non-delete events, return immediately
        if (count($playerEvents) <= 0) {
            return $numStaged;
        }
        
        // break apart player events into chunks (batches)
        $playerEventBatches = array_chunk($playerEvents, $this->batchSize);
        $batchNum = 0;
        
        foreach ($playerEventBatches as $playerEventBatch) {
            $batchNum++;

            // create collection of the record IDs (player ID)
            $playerIds = collect($playerEventBatch)->map(function ($playerEvent) {
                return $playerEvent['record_id'];
            })->toArray();
            
            // get all player records for [ playerIds ]
            $players = Player::whereIn('_id', $playerIds)->get();
            
            try {
                // wrap in transaction and rollback in case of failure
                DB::connection('mysql-dw')->transaction(function () use ($players) {
                    foreach ($players as $player) {
                        DB::connection('mysql-dw')->insert('insert into stg_player_events
                            (                            
                                player_id, 
                                id_usafb, 
                                id_external, 
                                name_first, 
                                name_middle, 
                                name_last, 
                                dob, 
                                gender, 
                                zip, 
                                years_experience, 
                                opt_in_marketing, 
                                created_date, 
                                updated_date,
                                created_at
                            ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())', [
                                $player->id,
                                $player->id_usafb,
                                $player->id_external,
                                $player->name_first,
                                $player->name_middle,
                                $player->name_last,
                                $player->dob,
                                $player->gender,
                                $player->address->postal_code,
                                $player->years_experience,
                                $player->opt_in_marketing,
                                $player->created_date,
                                $player->updated_date
                        ]);
                    
                        // loop through and load registrations
                        foreach ($player->registrations as $registration) {
                            DB::connection('mysql-dw')->insert('insert into stg_player_reg_events
                                (                            
                                    player_id,
                                    registration_id,
                                    registration_date,
                                    level,
                                    level_type,
                                    position,
                                    organization_name,
                                    organization_state,
                                    league_name,
                                    season_year,
                                    season,
                                    school_name,
                                    school_district,
                                    school_state,
                                    created_at
                                ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())', [
                                    $player->id,
                                    $registration->id,
                                    $registration->date,
                                    $registration->level,
                                    $registration->level_type,
                                    $registration->position,
                                    $registration->organization_name,
                                    $registration->organization_state,
                                    $registration->league_name,
                                    $registration->season_year,
                                    $registration->season,
                                    $registration->school_name,
                                    $registration->school_district,
                                    $registration->school_state
                            ]);
                        }
                    }
                });
                
                // remove events, no exceptions occurred, so assume staging load worked.
                $playerEventIds = collect($playerEvents)->map(function ($playerEvent) {
                    return $playerEvent['_id'];
                })->toArray();
                
                if (count($playerEventIds) > 0) {
                    RecordEvent::whereIn('_id', $playerEventIds)->delete();
                }
                
                // increment num of records staged (count)
                $numStaged += count($players);
            } catch (Exception $ex) {
                Log::error('Error staging Player events batch ('.$batch.') to DataWarehouse.');
                Log::error($ex->getMessage());
            }
        }
       
        return $numStaged;
    }
}
