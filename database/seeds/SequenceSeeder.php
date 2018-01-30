<?php

use Illuminate\Database\Seeder;

use App\Models\Sequence;

class SequenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // remove all sequence records
        Sequence::truncate();
        
        // create for player and coach
        $playerSequence = new Sequence([ '_id' => 'players' ]);
        $playerSequence->save();        
        $coachSequence = new Sequence([ '_id' => 'coaches' ]);
        $coachSequence->save();        
    }
}