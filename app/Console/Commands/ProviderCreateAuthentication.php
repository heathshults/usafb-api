<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Models\Role;
use App\Http\Controllers\UsersController;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class ProviderCreateAuthentication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provider:create:authentication {provider_id}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test authentication header for provider';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $providerId = $this->argument('provider_id');
        if (is_null($providerId)) {
            $this->error('Invalid or missing Provider ID (provider_id)');
            return false;
        }

        $provider = Provider::find($providerId);
        if (is_null($provider)) {
            $this->error('Unable to find Provider with ID ('.$providerId.')');
            return false;
        }
        
        if (is_null($provider->api_key)) {
            $this->error('Invalid or missing API Key.');
            return false;
        }

        $body = $this->ask('Payload Body?');
        if (is_null($body)) {
            $this->error('Invalid or missing payload body.');
            return false;
        }
        
        $authHeaders = app('ApiKey')->generateAuthenticationHeaders($provider->id, $body);
        $this->info('Generated header for Provider ('.$providerId.
            ') with API Key ('.$provider->api_key.'): '.var_export($authHeaders, true));
    }
}
