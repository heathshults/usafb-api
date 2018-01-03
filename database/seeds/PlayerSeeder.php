<?php

use Illuminate\Database\Seeder;

use App\Models\Player;
use App\Models\PlayerRegistration;
use App\Models\Guardian;
use App\Models\Address;

use App\Http\Services\Elasticsearch\ElasticsearchService;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $es = new ElasticsearchService();
        $es->deletePlayerIndices();
        $es->createPlayerIndices();            
        
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

            $address = new Address();
            $address->street_1 = '1234 Main St';
            $address->street_2 = 'Apt #12345';
            $address->city = 'Frisco';
            $address->state = 'TX';
            $address->county = 'Denton';
            $address->postal_code = '75034';
            $address->country = 'US';            
            $player->address()->associate($address);
            
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

            $player_registration1 = new PlayerRegistration();            
            $player_registration1->current = false;
            $player_registration1->positions = [ 'quarterback', 'running_back', 'fullback' ];
            $player_registration1->id_external = 'external_id_reg';
            $player_registration1->level = 'youth';
            $player_registration1->level_type = 'youth_flag';
            $player_registration1->organization_name = 'Organization Name '.$i.'_not_current';
            $player_registration1->organization_state = 'TX';
            $player_registration1->league_name = 'Frisco Little League';
            $player_registration1->season_year = 2016;
            $player_registration1->season = 'spring';
            $player_registration1->school_name = 'Frisco';
            $player_registration1->school_district = 'Frisco ISD';
            $player_registration1->school_state = 'TX';
            $player_registration1->team_name = 'Texas Rangers';
            $player_registration1->team_gender = 'M';     

            $player_registration2 = new PlayerRegistration();            
            $player_registration2->current = true;
            $player_registration2->positions = [ 'quarterback', 'running_back', 'fullback' ];
            $player_registration2->id_external = 'external_id_reg_current';
            $player_registration2->level = 'youth';
            $player_registration2->level_type = 'youth_flag';
            $player_registration2->organization_name = 'Organization Name '.$i.'_current';
            $player_registration2->organization_state = 'TX';
            $player_registration2->league_name = 'Frisco Little League';
            $player_registration2->season_year = 2017;
            $player_registration2->season = 'spring';
            $player_registration2->school_name = 'Frisco';
            $player_registration2->school_district = 'Frisco ISD';
            $player_registration2->school_state = 'TX';
            $player_registration2->team_name = 'Texas Rangers';
            $player_registration2->team_gender = 'M';        
                
            $player->registrations()->associate($player_registration1);
            $player->registrations()->associate($player_registration2);
            
            $player->save();            
        }
    }
}