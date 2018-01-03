<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Address;
use App\Http\Controllers\UsersController;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class UserDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete {email}';
    protected $client;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete user and accompanying Cognito account';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        if (is_null($email)) {
            $this->error("Invalid or missing user (email).");
        }
        
        $user = User::where([ "email" => $email ])->first();
        if (is_null($user)) {
            $this->error('Error finding user with email ('.$email.')');
            return false;
        }
        
        try {
            $authService = app('Auth');
            $authService->deleteUser($user->email);
            if ($user->delete()) {
                $this->info('User ('.$email.') successfully deleted.');
            } else {
                $this->error('Unable to delete User ('.$email.')');
            }
            return true;
        } catch (Exception $ex) {
            $this->error("Error occurred while deleting user.");
            $this->error($ex->getMessage());
            return false;
        }
    }
}
