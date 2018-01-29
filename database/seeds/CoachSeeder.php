<?php

use Illuminate\Database\Seeder;

use App\Models\Coach;
use App\Models\CoachRegistration;
use App\Models\Guardian;
use App\Models\Address;

use App\Http\Services\Elasticsearch\ElasticsearchService;

class CoachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {        
        // remove all coach records
        $es = new ElasticsearchService();
        $es->deleteCoachIndices();
        $es->createCoachIndices();       
        
        Coach::truncate();
        CoachRegistration::truncate();
                
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
        $positions = [ 'head_coach', 'quaterback_coach', 'wide_receiver_coach', 'linebacker_coach', 'offensive_coordinator', 'special_teams', 'assistant_coach', 'tight_end_coach', 'running_back_coach', 'defensive_back_coach', 'defensive_cooridnator', 'not_available' ];
        $genders = [ 'M', 'F', 'NA' ];
        $seasons = [ 'fall', 'spring', 'summer', 'winter' ];            

        for($i = 1; $i <= 500; $i++) {            
            $uid = uniqid();
            
            $nameMiddle = $this->randValue($middleNames);
            $nameLast = $this->randValue($lastNames);                        
            $gender = $this->randValue($genders);
            $nameFirst = null;
            if ($gender == 'F') {
                $nameFirst = $this->randValue($firstNamesF);                
            } else {
                $nameFirst = $this->randValue($firstNames);
            }

            $level = $this->randValue($levels);
            $levelType = $this->randValue($levelTypes);
            $street1 = $this->randValue($streets1);
            $street2 = $this->randValue($streets2);            
            $city = $this->randValue($cities);
            $zip = $this->randValue($zips);
            $county = $this->randValue($counties);
            
            $coach = new Coach();
            $coach->id_external = 'external_id_'.$i;            
            $coach->id_salesforce = 'salesforce_'.$i;            
            $coach->name_first = $nameFirst;
            $coach->name_middle = $nameMiddle;
            $coach->name_last = $nameLast;
            $coach->dob = date("Y-m-d", strtotime("-1 month", strtotime("2010-01-01")));
            $coach->gender = $gender;
            $coach->email = $nameFirst.'.'.$nameLast.$i.'@gmail.com';
            $coach->phone_home = '123-123-1234';
            $coach->phone_mobile = '123-123-1234';
            $coach->phone_work = '123-123-1234';
            $coach->social_twitter = '@.'.$nameFirst.$i;
            $coach->social_instagram = $nameFirst.$nameLast.$i;
            $coach->opt_in_marketing = true;
            $coach->years_experience = rand(0,20);
            $coach->organization_name = 'Organization Name '.$i;
            $coach->organization_state = 'TX';

            $address = new Address();
            $address->street_1 = rand(1000,9999).' '.$this->randValue($streets1);
            $address->street_2 = $street2;
            $address->city = $city;
            $address->state = 'TX';
            $address->county = $county;
            $address->postal_code = $zip;
            $address->country = 'US';
            $coach->address()->associate($address);

            $schoolName = $this->randValue($cities);
            $position = $this->randValue($positions);
            $season = $this->randValue($seasons);

            $registrationDate = strftime('%Y-%m-%d',strtotime('2016-'.rand(1,12).'-'.rand(1,28)));
            
            $coach_registration = new CoachRegistration();    
            $coach_registration->coach_id = $coach->id;
            $coach_registration->date = $registrationDate;
            $coach_registration->id_external = 'external_id_reg_'.$i;
            $coach_registration->id_usafb = 'usafb_id_'.$i;
            $coach_registration->id_salesforce = $coach->id_salesforce;
            $coach_registration->current = true;
            $coach_registration->position = $position;
            $coach_registration->level = $level;
            $coach_registration->level_type = $levelType;
            $coach_registration->certifications = [ 'CPR' ];
            $coach_registration->organization_name = 'Organization Name '.$i;
            $coach_registration->organization_state = 'TX';
            $coach_registration->league_name = $schoolName.' Little League';
            $coach_registration->season_year = rand(2015,2018);
            $coach_registration->season = $season;
            $coach_registration->school_name = $schoolName.' High School';
            $coach_registration->school_district = $schoolName.' ISD';
            $coach_registration->school_state = 'TX';
            $coach_registration->team_name = 'Texas Rangers';
            $coach_registration->team_gender = $gender;
            
            $coach->registrations()->associate($coach_registration);
            $coach->save();
        }
    }
    
    function randValue(&$values) {
        return $values[rand(0,count($values)-1)];
    }    
}