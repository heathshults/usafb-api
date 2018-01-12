<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Address;
use App\Http\Controllers\UsersController;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';
    protected $client;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Superuser account with accompanying Cognito account.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // get Superuser role from mongo
        $role = Role::where([ 'name' => 'Superuser' ])->first();
        
        if (!is_null($role)) {
            $user = new User();
            $user->role_id = $role->id;
            $user->name_first = $this->ask('First Name?');
            $user->name_last = $this->ask('Last Name?');
            $user->email = $this->ask('Email?');
            
            $address = new Address();
            $address->street_1 = $this->ask('Address Street 1');
            $address->city = $this->ask('City');
            $address->state = $this->ask('State');
            $address->postal_code = $this->ask('Postal Code');
            $user->address()->associate($address);
            
            if (!$user->valid()) {
                $this->info('The following errors occurred while creating user record:');
                $errors = $user->errors()->all();
                foreach ($errors as $error) {
                    $this->error($error);
                }
            } else {
                $this->info('Creating Cognito record for ('.$user->email.').');
                try {
                    // create record in Cognito, return Cognito ID if successful
                    $authService = app('Auth');
                    $idCognito = $authService->createUser($user->email);
                    if (!is_null($idCognito)) {
                        // set Cognito ID and save new user record
                        $user->id_cognito = $idCognito;
                        $user->save();
                        $this->info('User ('.$user->id.') with Cognito ID ('.$idCognito.') successfully created.');
                    } else {
                        $this->error('Error creating record in Cognito.');
                    }
                } catch (Exception $ex) {
                    $this->error($ex->getMessage());
                }
            }
        } else {
            $this->error('Missing Administrator Role. Role must exist before adding users.');
        }
        
        return false;
    }
}
