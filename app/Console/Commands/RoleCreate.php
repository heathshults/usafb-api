<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Http\Controllers\UsersController;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class RoleCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:create {name}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new Role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Available: '.implode(', ', Role::PERMISSIONS));
        $providedPermissions = $this->ask('Enter Permissions (separate multiple with comma):');
        
        if (is_null($providedPermissions)) {
            $this->error('Invalid or missing permissions.');
            return false;
        }
        
        $permissions = [];
        foreach (explode(',', $providedPermissions) as $permission) {
            $trimmedPermission = trim($permission);
            $permissions[] = $trimmedPermission;
        }
        if (count($permissions) <= 0) {
            $this->error('Invalid or missing permissions.');
        }
                
        $role = new Role();
        $role->name = $this->argument('name');
        $role->permissions = $permissions;

        if ($role->valid() && $role->save()) {
            $this->info('Role ('.$role->name.') with ID ('.$role->id.') successfully created.');
            return true;
        } else {
            $this->error('Unable to create Role. Please check values and try again.');
            return false;
        }
    }
}
