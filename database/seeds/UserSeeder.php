<?php

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // remove all user records
        User::truncate();
        
        for($i = 1; $i <= 120; $i++) {
            $uid = uniqid();            
            $user = new User();
            $user->id_external = $uid;
            $user->name_first = 'John '.$i;
            $user->name_last = 'Doe';
            $user->phone = '123-123-1234';
            $user->email = 'john.doe'.$uid.'@gmail.com';            

            $role = Role::first();
            $user->role_id = $role->id;
            $user->save();        
        }
        
        $role = Role::first();
        $role->permissions = [ 'test' ];
        $role->save();
    }
}
