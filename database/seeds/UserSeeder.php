<?php

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Address;
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

        // set default (superuser) role
        $role = Role::where([ 'name' => 'Superuser' ])->first();
        
        for($i = 1; $i <= 10; $i++) {
            $uid = uniqid();            

            $user = new User();            
            $user->role_id = $role->id;            
            $user->id_external = $uid;
            $user->name_first = 'John '.$i;
            $user->name_last = 'Doe';
            $user->phone = '123-123-1234';
            $user->email = 'john.doe'.$uid.'@gmail.com';            
            
            $address = new Address();
            $address->street_1 = '1234 Main St';
            $address->city = 'Frisco';
            $address->state = 'TX';
            $address->postal_code = '75034';
            $address->country = 'US';                
            $user->address()->associate($address);                        
            
            $user->save();
        }        
    }
}
