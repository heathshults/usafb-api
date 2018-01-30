<?php

namespace App\Console\Commands;

use App\Console\Commands\Warehouse\CoachStager;
use App\Console\Commands\Warehouse\PlayerStager;
use App\Models\Coach;
use App\Models\CoachRegistration;
use App\Models\Player;
use App\Models\PlayerRegistration;
use App\Models\RecordEvent;

use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;

class WarehouseStage extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:stage {batch_size=100}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load/stage pending records to warehouse';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // size of batches to use when loading data
        $batchSize = $this->argument('batch_size');
                
        $this->info('Starting Warehouse stage load. Using batch size of ('.$batchSize.').');
        $this->info('');

        $this->info('Locking process.');
        if (!$this->lockProcess()) {
            $this->info('Unable to obtain lock. Process lock exists. Exiting.');
            return true;
        }
        
        try {
            // stage players updates & removals
            $this->info('Staging Player records to Warehouse.');
            $playerStager = new PlayerStager($batchSize);
            $this->info('Staging Player record updates.');
            $numPlayerUpdatesStaged = $playerStager->stageUpdates();
            $this->info('Staged ('.$numPlayerUpdatesStaged.') Player record updates.');
            $this->info('Staging Player record removals.');
            $numPlayerRemovalsStaged = $playerStager->stageRemovals();
            $this->info('Staged ('.$numPlayerRemovalsStaged.') Player record removals.');

            $this->info('');
        
            // stage coaches updates & removals
            $this->info('Staging Coach records to Warehouse.');
            $coachStager = new CoachStager($batchSize);
            $this->info('Staging Coach record updates.');
            $numCoachUpdatesStages = $coachStager->stageUpdates();
            $this->info('Staged ('.$numCoachUpdatesStages.') Coach record updates.');
            $this->info('Staging Coach record removals.');
            $numCoachRemovalsStaged = $coachStager->stageRemovals();
            $this->info('Staged ('.$numCoachRemovalsStaged.') Coach record removals.');
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        } finally {
            $this->info('Unlocking process.');
            $this->unlockProcess();
        }
        
        $this->info('');
        $this->info('Finsihed Warehouse stage load.');
        return true;
    }
}
