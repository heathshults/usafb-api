<?php

namespace App\Console\Commands\Warehouse;

interface StagerInterface
{
    /**
     * Constructor
     *
     * @param int size of batches to process in
     */
    public function __construct(int $batchSize = 100);

    /**
     * Get number of events ready to be staged
     *
     * @return int number of events to stage
     */
    public function numEvents() : int;
        
    /**
     * Stage pending event record removals to Datawarehouse staging tables
     *
     * @return int number of results staged for removal
     */
    public function stageRemovals(array $options = []) : int;
    
    /**
     * Stage pending Player/Coach events to Datawarehouse staging tables
     *
     * @return int number of results staged
     */
    public function stageUpdates(array $options = []) : int;
}
