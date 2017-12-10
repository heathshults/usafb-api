<?php

use App\Models\Player;
use App\Models\Guardian;
use App\Models\Address;

class PlayerTest extends TestCase
{
    public function testInstantiatingPlayerModel()
    {
        $player = new App\Models\Player();
        $this->assertNotNull($player);
    }

    public function testSettingPlayerModelAttributes() 
    {
        $player = new Player();
        $player->id_external = '12345';
        $player->name_first = 'John';
        $player->name_last = 'Doe';
        $player->dob = Date('now');
        $player->grade = 9;
        $player->graduation_year = 2025;
        $player->gender = 'M';
        $player->height_ft = 4;
        $player->height_in = 9;
        $player->weight = 120;
        $player->email = 'john.doe@gmail.com';
        $player->phone_home = '123-123-1234';
        $player->phone_mobile = '123-123-1234';
        $player->phone_work = '123-123-1234';
        $player->social_twitter = '@johndoe';
        $player->social_instagram = 'johndoeing';
        $player->opt_in_marketing = true;
        $player->sports = [ 'basketball' ];
        $player->years_experience = 5;

        // address (1)
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
        $guardian1->name_first = 'JJ';
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
    
        // guardian 2 w/o address
        $guardian2 = new Guardian();
        $guardian2->name_first = 'Jane';
        $guardian2->name_middle = 'Sarah';
        $guardian2->name_last = 'Doe';
        $guardian2->phone_home = '123-123-1234';
        $guardian2->phone_work = '123-123-1234';
        $guardian2->phone_mobile = '123-123-1234';
        $guardian2->opt_in_marketing = false;
        $player->guardians()->associate($guardian2);

        $this->assertNotNull($player);
        $this->assertSame('John', $player->name_first);
        $this->assertSame('Doe', $player->name_last);
        $this->assertSame('12345', $player->id_external);
        $this->assertNotNull($player->address);
        $this->assertNotNull($player->address->street_1);
        $this->assertNotNull($player->guardians()->first()->address->street_1);
        $this->assertNotNull($player->guardians);
        $this->assertEquals(2, $player->guardians->count());
        $this->assertEquals('1234 Main St', $player->guardians()->first()->address->street_1);
    }
}
