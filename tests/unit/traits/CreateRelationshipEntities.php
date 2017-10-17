<?php

namespace Tests\Unit\Traits;

use App\Models\Registrant;
use App\Models\Registration;
use App\Models\Source;
use App\Models\PlayerRegistration;

trait CreateRelationshipEntities
{
    /**
     * Creates a new Source entity.
     *
     * @return Source
     */
    public function getSource() {
        $entity = new Source;
        $entity->name = 'Source Name Test' ;
        $entity->api_key = 'ThisIsMyTestApiKey';

        $entity->save();

        return $entity;
    }

    /**
     * Creates a new Registrant entity.
     *
     * @return Registrant
     */
    public function getRegistrant() {
        $entity = new Registrant;
        $entity->type = 'PLAYER' ;
        $entity->first_name = 'Some name';
        $entity->middle_name = 'Middle name';
        $entity->last_name = 'Last Name';
        $entity->email = 'mail@mail.com';
        $entity->gender = 'Male';
        $entity->city = 'California';
        $entity->zip_code = '234141234123';
        $entity->birth_date = '11/27/1984';
        $entity->phone_number = '1234567890';
        $entity->game_type = 'SOME';
        $entity->level = 'LEVEL';
        $entity->state = 'CALIFORNIA';
        $entity->address_first_line = 'An Address 1234';
        $entity->county = 'A county';

        $entity->save();

        return $entity;
    }

    /**
     * Creates a new Registration entity.
     *
     * @return Registration
     */
    public function getRegistration() {
        $entity = new Registration;
        $entity->source_id = $this->getSource()->id;
        $entity->registrant_id = $this->getRegistrant()->id;
        $entity->type = 'PLAYER' ;
        $entity->league = 'league';
        $entity->org_name = 'Oranization...';
        $entity->org_state = 'NY';
        $entity->season = '2017';
        $entity->external_id = 'myexternalid';
        $entity->right_to_market = true;
        $entity->team_gender = 'Male';
        $entity->team_name = 'A-Team';
        $entity->school_district = 'school district';
        $entity->school_state = 'school sate';
        //Registrant fields
        $entity->first_name = 'Firt name';
        $entity->middle_name = 'Middle name';
        $entity->last_name = 'Last Name';
        $entity->email = 'mail@mail.com';
        $entity->gender = 'Male';
        $entity->city = 'California';
        $entity->zip_code = '234141234123';
        $entity->birth_date = '11/27/1984';
        $entity->phone_number = '1234567890';
        $entity->game_type = 'SOME';
        $entity->level = 'LEVEL';
        $entity->state = 'CALIFORNIA';
        $entity->address_first_line = 'An Address 1234';
        $entity->county = 'A county';

        $entity->save();

        return $entity;
    }

    /**
     * Creates a new PlayerRegistration entity.
     *
     * @return PlayerRegistration
     */
    public function getPlayerRegistration() {

        $entity = new PlayerRegistration;
        $entity->registration_id = $this->getRegistration()->id;
        $entity->positions = 'positions...';
        $entity->team_age_group = '2017';
        $entity->school_name = 'school name...';
        $entity->grade = 'K-12';
        $entity->height = '5.3';
        $entity->graduation_year = '2018';
        $entity->instagram = '@instagram';
        $entity->sports = 'my sports...';
        $entity->twitter = '@twitter';
        $entity->weight = '10';
        $entity->years_at_sport = '3';

        $entity->save();

        return $entity;
    }


}
