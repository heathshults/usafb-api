<?php

use Illuminate\Database\Seeder;

use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // remove all player records
        Role::truncate();
        
        $role = new Role();
        $role->name = "Test";
        $role->permissions = [ "create_user", "view_user" ];
        $role->save();
    }
}
