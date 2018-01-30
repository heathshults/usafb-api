<?php

namespace App\Console\Commands\Warehouse;

interface LoaderInterface
{
    /**
     * Load staged Coach/Player data into Coach/Player dimension
     *
     * @return int number of results loaded
     */
    public function load(array $options = []);
}
