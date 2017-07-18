<?php

use Illuminate\Database\Seeder;

class GameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $playerLevel = array('YOUTH FLAG', '7ON7', 'MODIFIED TACKLE', '11-PLAYER TACKLE', 'ADULT FLAG', 'OTHER');

        foreach ($playerLevel as $level) {
            DB::table('game_type')->insert([
                'name' => $level
            ]);
        }
    }
}
