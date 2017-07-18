<?php

use Illuminate\Database\Seeder;

class PlayerLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $playerLevel = array('YOUTH', 'MIDDLE SCHOOL', 'FRESHMAN', 'JV', 'VARSITY', 'AMATEUR/ADULT');

        foreach ($playerLevel as $level) {
            DB::table('player_level')->insert([
                'name' => $level
            ]);
        }
    }
}
