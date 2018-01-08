<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Address;
use App\Http\Controllers\UsersController;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class RoleList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:list';
    protected $client;
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all roles in system';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $roles = Role::all();
        foreach($roles as $role) {
            $this->info('Role ('.$role->id.') '.$role->name.' : '.implode(', ', $role->permissions));
        }
    }
}
