<?php

namespace App\Console\Commands;

use App\Console\Commands\Warehouse\CoachLoader;
use App\Console\Commands\Warehouse\CoachRegistrationLoader;
use App\Console\Commands\Warehouse\PlayerLoader;
use App\Console\Commands\Warehouse\PlayerRegistrationLoader;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;

class WarehouseLoad extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:load';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load/process staged records in warehouse';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting Warehouse load.');
        $this->info('');
     
        $this->info('Locking process.');
        if (!$this->lockProcess()) {
            $this->info('Unable to obtain lock. Process lock exists. Exiting.');
            return true;
        }
        
        try {
            // players
            $this->info('Loading staged Player records.');
            $playerLoader = new PlayerLoader();
            $playerLoader->load();
            $this->info('Finished loading Player records.');

            $this->info('');

            // player registrations
            $this->info('Loading staged Player Registration records.');
            $playerRegLoader = new PlayerRegistrationLoader();
            $playerRegLoader->load();
            $this->info('Finished loading Player Registration records.');

            $this->info('');

            // coaches
            $this->info('Loading staged Coach records.');
            $coachLoader = new CoachLoader();
            $coachLoader->load();
            $this->info('Finished loading Coach records.');

            $this->info('');

            // coach registrations
            $this->info('Loading staged Coach Registration records.');
            $coachRegLoader = new CoachRegistrationLoader();
            $coachRegLoader->load();
            $this->info('Finished loading Coach Registration records.');

            $this->info('');
        } catch (Exception $ex) {
            $this->error($ex->getMessage());
        } finally {
            $this->info('Unlocking process.');
            $this->unlockProcess();
        }

        $this->info('Finsihed Warehouse load.');
    }
}
