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
        
        $firstNames = [ 'Jim', 'Jason', 'Billy', 'Brandon', 'Chris', 'JJ', 'Marky', 'Mike', 'JD', 'Don' ];
        $firstNamesF = [ 'Sara', 'Samantha', 'Veronika', 'Shelly', 'Christa', 'Jenny', 'Jamie' ];
        $middleNames = [ 'Lee', 'JJ', 'Sam', 'Smith', null, null ];
        $lastNames = [ 'Smith', 'Johnson', 'Simons', 'Geren', 'Tompkins', 'Travis', 'Kawahara', 'Mitrakos', 'Boll' ];
        $levels = [ 'youth', 'middle_school', 'freshman', 'jv', 'varsity', 'college', 'professional' ];
        $levelTypes = [ 'youth_flag', '7on7', 'rookie_tackle', '11_player_tackle', 'adult_flag', 'other', 'flex' ];
        $cities = [ 'Frisco', 'Plano', 'Dallas', 'Allen', 'McKinney', 'Richardson', 'Garland', 'Rockwall' ];
        $streets1 = [ 'Main St', 'Sunshine Dr', 'Sunset Dr', 'Rolling Dr', '5th St' ];
        $streets2 = [ 'Apt #1234', 'Suit #521', null, null ];
        $zips = [ '75034', '75070', '75071', '75035', '75206', '75201' ];
        $counties = [ 'Denton', 'Collin', 'Dallas' ];        
        $positions = [ 'quarterback', 'center', 'running_back', 'fullback', 'wide_receiver', 'tight_end', 'left_guard', 'right_guard', 'left_tackle', 'right_tackle', 'defensive_tackle', 'defensive_end', 'linebacker', 'safety', 'cornerback', 'punter', 'not_available' ];
        $genders = [ 'M', 'F', 'NA' ];
        
        for($i = 1; $i <= 500; $i++) {
            $uid = uniqid();    
            
            $nameLast = $this->randValue($lastNames);
            $gender = $this->randValue($genders);            
            $nameFirst = null;
            
            if ($gender == 'F') {
                $nameFirst = $this->randValue($firstNamesF);                
            } else {
                $nameFirst = $this->randValue($firstNames);
            }
            
            $player = new Player();
            $player->id_external = $i;
            $player->id_salesforce = 'salesforce_'.$i;            
            $player->name_first = $nameFirst;
            $player->name_middle = $this->randValue($middleNames);
            $player->name_last = $nameLast;
            $player->dob = date("Y-m-d", strtotime("-1 month", strtotime("2010-01-01")));

            $player->grade = rand(4,12);
            $player->graduation_year = rand(2000,2025);
            $player->gender = $gender;
            $player->height_ft = rand(4,6);
            $player->height_in = rand(0,11);
            $player->weight = rand(80,200);

            $player->email = $nameFirst.'.'.$nameLast.$i.'@gmail.com';
            $player->phone_home = '123-123-1234';
            $player->phone_mobile = '123-123-1234';
            $player->phone_work = '123-123-1234';
            $player->social_twitter = '@.'.$nameFirst.$i;
            $player->social_instagram = $nameFirst.$nameLast.$i;
            $player->opt_in_marketing = true;

            $player->sports = [ 'basketball', 'football' ];
            $player->years_experience = rand(0,10);

            $address = new Address();
            $address->street_1 = rand(1000,9999).' '.$this->randValue($streets1);
            $address->street_2 = $this->randValue($streets2);
            $address->city = $this->randValue($cities);
            $address->state = 'TX';
            $address->county = $this->randValue($counties);
            $address->postal_code = $this->randValue($zips);
            $address->country = 'US';            
            $player->address()->associate($address);
            
            // guardian 1 w/ address
            $guardian1 = new Guardian();
            $guardian1->name_first = $this->randValue($firstNames);
            $guardian1->name_middle = $this->randValue($middleNames);
            $guardian1->name_last = $nameLast;
            $guardian1->phone_home = '123-123-1234';
            $guardian1->phone_work = '123-123-1234';
            $guardian1->phone_mobile = '123-123-1234';
            $guardian1->opt_in_marketing = true;
            
            // guardian address
            $guardian1Address = new Address();
            $guardian1Address->street_1 = rand(1000,9999).' '.$this->randValue($streets1);
            $guardian1Address->street_2 = $this->randValue($streets2);
            $guardian1Address->city = $this->randValue($cities);
            $guardian1Address->state = 'TX';
            $guardian1Address->county = $this->randValue($counties);
            $guardian1Address->postal_code = $this->randValue($zips);
            $guardian1Address->country = 'US';
            $guardian1->address()->associate($guardian1Address);
                        
            $player->guardians()->associate($guardian1);

            $schoolName = $this->randValue($cities);
            
            $registrationDay1 = rand(1,28);
            $registrationDay2 = rand(1,28);
            $registrationMonth1 = rand(1,12);
            $registrationMonth2 = rand(1,12);    
            $registrationDate1 = strftime('%Y-%m-%d',strtotime('2016-'.$registrationMonth1.'-'.$registrationDay1));
            $registrationDate2 = strftime('%Y-%m-%d',strtotime('2017-'.$registrationMonth2.'-'.$registrationDay2));
            
            $playerRegistration1 = new PlayerRegistration();            
            $playerRegistration1->current = false;
            $playerRegistration1->$registrationDate1;
            $playerRegistration1->position = $this->randValue($positions);
            $playerRegistration1->id_external = 'external_id_reg';
            $playerRegistration1->level = $this->randValue($levels);
            $playerRegistration1->level_type = $this->randValue($levelTypes);
            $playerRegistration1->organization_name = 'Organization Name '.$i.'_not_current';
            $playerRegistration1->organization_state = 'TX';
            $playerRegistration1->league_name = 'Frisco Little League';
            $playerRegistration1->season_year = 2016;
            $playerRegistration1->date = '2016-01-20';
            $playerRegistration1->season = 'spring';
            $playerRegistration1->school_name = $schoolName;
            $playerRegistration1->school_district = $schoolName.' ISD';
            $playerRegistration1->school_state = 'TX';
            $playerRegistration1->team_name = 'Texas Rangers';
            $playerRegistration1->team_gender = $gender;

            $playerRegistration2 = new PlayerRegistration();            
            $playerRegistration2->current = true;
            $playerRegistration2->$registrationDate2;
            $playerRegistration2->position = 'running_back';
            $playerRegistration2->id_external = 'external_id_reg_current';
            $playerRegistration2->level = $this->randValue($levels);
            $playerRegistration2->level_type = $this->randValue($levelTypes);
            $playerRegistration2->organization_name = 'Organization Name '.$i.'_current';
            $playerRegistration2->organization_state = 'TX';
            $playerRegistration2->league_name = 'Frisco Little League';
            $playerRegistration2->season_year = 2017;
            $playerRegistration2->date = '2017-01-20';
            $playerRegistration2->season = 'spring';
            $playerRegistration2->school_name = $schoolName;
            $playerRegistration2->school_district = $schoolName.' ISD';
            $playerRegistration2->school_state = 'TX';
            $playerRegistration2->team_name = 'Texas Rangers';
            $playerRegistration2->team_gender = $gender;       
                
            $player->registrations()->associate($playerRegistration1);
            $player->registrations()->associate($playerRegistration2);
            
            $player->save();
        }
    }
    
    function randValue(&$values) {
        return $values[rand(0,count($values)-1)];
    }
}