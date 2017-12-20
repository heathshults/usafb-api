<?php

use Illuminate\Database\Seeder;

use App\Models\Coach;
use App\Models\CoachRegistration;
use App\Models\Guardian;
use App\Models\Address;

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
        Coach::truncate();
        CoachRegistration::truncate();
        
        for($i = 1; $i <= 500; $i++) {
            $uid = uniqid();       
            $coach = new Coach();
            $coach->id_external = 'external_id_'.$i;            
            $coach->id_salesforce = 'salesforce_'.$i;            
            $coach->name_first = 'John '.$i;
            $coach->name_last = 'Doe';
            $coach->dob = date("Y-m-d", strtotime("-1 month", strtotime("2010-01-01")));
            $coach->email = 'john.doe'.$uid.'@gmail.com';
            $coach->phone_home = '123-123-1234';
            $coach->phone_mobile = '123-123-1234';
            $coach->phone_work = '123-123-1234';
            $coach->social_twitter = '@johndoe.'.$uid;
            $coach->social_instagram = 'johndoeing'.$uid;
            $coach->opt_in_marketing = true;
            $coach->gender = 'M';
            $coach->email = 'john.doe'.$uid.'@gmail.com';
            $coach->years_experience = rand(0,20);
            $coach->organization_name = 'Organization Name '.$i;
            $coach->organization_state = 'TX';
            $coach->level = 'youth';
            $coach->level_type = 'youth_flag';
            $coach->positions = [ 'head_coach', 'quaterback_coach' ];

            $address = new Address();
            $address->street_1 = '1234 Main St';
            $address->street_2 = 'Apt #12345';
            $address->city = 'Frisco';
            $address->state = 'TX';
            $address->county = 'Denton';
            $address->postal_code = '75034';
            $address->country = 'US';
            $coach->address()->associate($address);
            
            $coach_registration = new CoachRegistration();            
            $coach_registration->coach_id = $coach->id;
            $coach_registration->id_external = 'external_id_reg_'.$i;
            $coach_registration->id_usafb = 'usafb_id_'.$i;
            $coach_registration->id_salesforce = $coach->id_salesforce;
            $coach_registration->current = true;
            $coach_registration->positions = $coach->positions;
            $coach_registration->level = $coach->level;
            $coach_registration->level_type = 'youth_flag';
            $coach_registration->certifications = [ 'CPR' ];
            $coach_registration->organization_name = 'Organization Name '.$i;
            $coach_registration->organization_state = 'TX';
            $coach_registration->league_name = 'Frisco Little League';
            $coach_registration->season_year = 2017;
            $coach_registration->season = 'Spring';
            $coach_registration->school_name = 'Frisco High School';
            $coach_registration->school_district = 'Frisco ISD';
            $coach_registration->school_state = 'TX';
            $coach_registration->team_name = 'Texas Rangers';
            $coach_registration->team_gender = 'M';
            
            $coach->registrations()->associate($coach_registration);
            $coach->save();
        }
    }
}