<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Models\Role;
use App\Http\Controllers\UsersController;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class ProviderList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provider:list';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all providers in system';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $providers = Provider::all();
        foreach ($providers as $provider) {
            $rolePermissions = 'None';
            if (!is_null($provider->role_permissions)) {
                $rolePermissions = implode(', ', $provider->role_permissions);
            }
            $this->info('Provider ('.$provider->id.') '.$provider->name.' API Key ('.
                $provider->api_key.') Role ('.$provider->role_name.') Permissions ('.$rolePermissions.')');
        }
    }
}
