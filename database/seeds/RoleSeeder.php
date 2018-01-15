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
        // remove all roles
        
        Role::truncate();
        
        // add default roles
        
        $tpRole = new Role();
        $tpRole->name = 'Provider';
        $tpRole->permissions = [
            'add_player',
            'add_coach',
            'update_coach',
            'update_player',
        ];
        $tpRole->save();
        
        $suRole = new Role();
        $suRole->name = 'Superuser';
        $suRole->permissions = [
            'stats',
            'export_players',
            'import_players',
            'import_coaches',
            'export_coaches',
            'manage_users',
            'view_dashboard',
            'view_players',
            'view_coaches',
            'delete_player',
            'delete_coach',
        ];
        $suRole->save();

        $adminRole = new Role();
        $adminRole->name = 'Administrator';
        $adminRole->permissions = [
            'stats',            
            'export_players',
            'import_players',
            'import_coaches',
            'export_coaches',
            'manage_users',
            'view_dashboard',
            'view_players',
            'view_coaches'
        ];
        $adminRole->save();

        $stakeholderRole = new Role();
        $stakeholderRole->name = 'Stakeholder';
        $stakeholderRole->permissions = [
            'stats',            
            'export_players',
            'import_players',
            'import_coaches',
            'export_coaches',
            'view_dashboard',
            'view_players',
            'view_coaches'
        ];        
        $stakeholderRole->save();
    }
}
