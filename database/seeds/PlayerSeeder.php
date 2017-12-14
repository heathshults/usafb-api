<?php

use Illuminate\Database\Seeder;

use App\Models\Player;
use App\Models\PlayerRegistration;
use App\Models\Guardian;
use App\Models\Address;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // remove all player records
        Player::truncate();
        PlayerRegistration::truncate();

        for($i = 1; $i <= 500; $i++) {
            $uid = uniqid();    
            
            $player = new Player();
            $player->id_external = $i;
            $player->id_salesforce = 'salesforce_'.$i;            
            $player->name_first = 'John '.$i;
            $player->name_last = 'Doe';
            $player->dob = date("Y-m-d", strtotime("-1 month", strtotime("2010-01-01")));

            $player->grade = rand(4,12);
            $player->graduation_year = rand(2000,2025);
            $player->gender = 'M';
            $player->height_ft = rand(4,6);
            $player->height_in = rand(0,11);
            $player->weight = rand(80,200);

            $player->email = 'john.doe'.$uid.'@gmail.com';
            $player->phone_home = '123-123-1234';
            $player->phone_mobile = '123-123-1234';
            $player->phone_work = '123-123-1234';
            $player->social_twitter = '@johndoe.'.$uid;
            $player->social_instagram = 'johndoeing'.$uid;
            $player->opt_in_marketing = true;

            $player->sports = [ 'basketball', 'football' ];
            $player->years_experience = rand(0,10);

            $player->address = new Address();
            $player->address->street_1 = '1234 Main St';
            $player->address->street_2 = 'Apt #12345';
            $player->address->city = 'Frisco';
            $player->address->state = 'TX';
            $player->address->county = 'Denton';
            $player->address->postal_code = '75034';
            $player->address->country = 'US';

            // guardian 1 w/ address
            $guardian1 = new Guardian();
            $guardian1->name_first = 'JJ '.$uid;
            $guardian1->name_middle = 'Mike';
            $guardian1->name_last = 'Doe';
            $guardian1->phone_home = '123-123-1234';
            $guardian1->phone_work = '123-123-1234';
            $guardian1->phone_mobile = '123-123-1234';
            $guardian1->opt_in_marketing = true;
            
            // guardian address
            $guardian1_address = new Address();
            $guardian1_address->street_1 = '1234 Main St';
            $guardian1_address->street_2 = 'Apt #12345';
            $guardian1_address->city = 'Frisco';
            $guardian1_address->state = 'TX';
            $guardian1_address->county = 'Denton';
            $guardian1_address->postal_code = '75034';
            $guardian1_address->country = 'US';
            $guardian1->address()->associate($guardian1_address);
            
            $player->guardians()->associate($guardian1);

            $player->save();
            
            $player_registration = new PlayerRegistration();            
            $player_registration->player_id = $player->id;            
            $player_registration->id_external = 'external_id_reg_'.$i;
            $player_registration->id_usafb = 'usafb_id_'.$i;
            $player_registration->id_salesforce = $player->id_salesforce;            
            $player_registration->current = true;
            $player_registration->level = 'youth';
            $player_registration->level_type = 'youth_flag';
            $player_registration->organization_name = 'Organization Name '.$i;
            $player_registration->organization_state = 'TX';
            $player_registration->league_name = 'Frisco Little League';
            $player_registration->season_year = 2017;
            $player_registration->season = 'spring';
            $player_registration->school_name = 'Frisco';
            $player_registration->school_district = 'Frisco ISD';
            $player_registration->school_state = 'TX';
            $player_registration->team_name = 'Texas Rangers';
            $player_registration->team_gender = 'M';
                                    
            $player_registration->save();
        }
    }
}