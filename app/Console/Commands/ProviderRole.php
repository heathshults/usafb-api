<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Models\Role;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class ProviderRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provider:role {provider_id} {role_id}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Role assigned to Provider.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $providerId = $this->argument('provider_id');
        if (is_null($providerId)) {
            $this->error('Invalid or missing Provider ID (provider_id).');
        }
                
        $provider = Provider::find($providerId);
        if (is_null($provider)) {
            $this->error('Error finding Provider with ID ('.$provider.')');
            return false;
        }
        
        $roleId = $this->argument('role_id');
        if (is_null($roleId)) {
            $this->error('Invalid or missing Role ID (role_id).');
        }
                
        $role = Role::find($roleId);
        if (is_null($role)) {
            $this->error('Error finding Role with ID ('.$roleId.')');
            return false;
        }
        
        $provider->role_id = $roleId;
        if ($provider->valid() && $provider->save()) {
            $this->info('Provider ('.$providerId.') successfully updated to Role ('.$roleId.').');
            return true;
        } else {
            $this->info('Error occurred updating Provider role:');
            $errors = $provider->errors();
            if (!is_array($errors)) {
                $errors = $errors->all();
            }
            foreach ($errors as $error) {
                $this->error($error);
            }
            return false;
        }
    }
}
