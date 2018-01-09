<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Models\Role;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class ProviderCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provider:create {role_id}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Provider record';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $roleId = $this->argument('role_id');
        
        if (is_null($roleId)) {
            $this->error('Invalid or missing role (role_id).');
        }
                
        $role = Role::find($roleId);
        if (is_null($role)) {
            $this->error('Error finding role with ID ('.$roleId.')');
            return false;
        }
        
        $provider = new Provider();
        $provider->name = $this->ask('Provider Name?');
        $provider->contact_name_first = $this->ask('Contact First Name?');
        $provider->contact_name_last = $this->ask('Contact Last Name?');
        $provider->contact_email = $this->ask('Contact Email?');
        $provider->contact_phone = $this->ask('Contact Phone?');
        $provider->role_id = $roleId;
        
        if ($provider->valid() && $provider->save()) {
            $this->info('Provider ('.$provider->id.') with API Key ('.$provider->api_key.') successfully created.');
            return true;
        } else {
            $this->info('The following errors occurred while creating Provider record:');
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
