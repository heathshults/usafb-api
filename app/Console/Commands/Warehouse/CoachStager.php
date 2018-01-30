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

class CoachStager implements StagerInterface
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
        return RecordEvent::where('record_type', 'coaches')
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
     * Stage pending Coach event records to Datawarehouse staging tables
     *
     * @return int number of results staged
     */
    public function stageUpdates(array $options = []) : int
    {
        $numStaged = 0;
        
        // find non-delete coach event records
        $coachEvents = RecordEvent::where('record_type', 'coaches')
            ->where('deleted', false)
            ->get(['record_id', 'deleted'])
            ->toArray();
        
        if (count($coachEvents) <= 0) {
            return $numStaged;
        }
        
        // break apart player events into chunks (batches)
        $coachEventBatches = array_chunk($coachEvents, $this->batchSize);
        $batchNum = 0;

        foreach ($coachEventBatches as $coachEventBatch) {
            $batchNum++;
            
            $coachIds = collect($coachEventBatch)->map(function ($coachEvent) {
                return $coachEvent['record_id'];
            })->toArray();
                
            try {
                $coaches = Coach::whereIn('_id', $coachIds)->get();

                DB::connection('mysql-dw')->transaction(function () use ($coaches) {
                    foreach ($coaches as $coach) {
                        // load coach profile records
                        DB::connection('mysql-dw')->insert('insert into stg_coach_events
                        (                            
                            coach_id, 
                            id_usafb, 
                            id_external, 
                            name_first, 
                            name_middle, 
                            name_last, 
                            dob, 
                            gender, 
                            zip,
                            email,
                            years_experience, 
                            opt_in_marketing, 
                            created_date, 
                            updated_date,
                            created_at
                        ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())', [
                            $coach->id,
                            $coach->id_usafb,
                            $coach->id_external,
                            $coach->name_first,
                            $coach->name_middle,
                            $coach->name_last,
                            $coach->dob,
                            $coach->gender,
                            $coach->address->postal_code,
                            $coach->email,
                            $coach->years_experience,
                            $coach->opt_in_marketing,
                            $coach->created_date,
                            $coach->updated_date
                        ]);
                                
                        foreach ($coach->registrations as $registration) {
                            DB::connection('mysql-dw')->insert('insert into stg_coach_reg_events
                            (                            
                                coach_id,
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
                                $coach->id,
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
                $coachEventIds = collect($coachEventBatch)->map(function ($coachEvent) {
                    return $coachEvent['_id'];
                })->toArray();
                
                if (count($coachEventIds) > 0) {
                    RecordEvent::whereIn('_id', $coachEventIds)->delete();
                }
                
                // increment num of records staged (count)
                $numStaged += count($coaches);
            } catch (Exception $ex) {
                $this->error($ex->getMessage());
            }
        }
        return $numStaged;
    }
}
