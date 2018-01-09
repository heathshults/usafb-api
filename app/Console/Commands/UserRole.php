<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Address;
use App\Http\Controllers\UsersController;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class UserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:role {email} {role_id}';
    protected $client;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change user with (email) to role with (role_id)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->argument('email');
        $roleId = $this->argument('role_id');
        
        if (is_null($email) || is_null($roleId)) {
            $this->error('Invalid or missing user (email) or role (role_id).');
        }
        
        $user = User::where([ "email" => $email ])->first();
        if (is_null($user)) {
            $this->error('Error finding user with email ('.$email.')');
            return false;
        }
        
        $role = Role::find($roleId);
        if (is_null($role)) {
            $this->error('Error finding role with ID ('.$roleId.')');
            return false;
        }
                
        try {
            $user->role_id = $role->id;
            if ($user->save()) {
                $this->info('User role successfully updated.');
                return true;
            } else {
                $this->error('Unable to update user role.');
                return false;
            }
        } catch (Exception $ex) {
            $this->error('Error setting user ('.$email.') role to ('.$roleId.').');
            $this->error($ex->getMessage());
            return false;
        }
    }
}
