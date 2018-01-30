<?php

namespace App\Http\Services;

use Jenssegers\Optimus\Optimus;
use Jenssegers\Optimus\Energon;
use App\Models\IdSequence;
use Log;

/**
* Id obfuscation based on Knuth's multiplicative hashing method. This function uses
* the database table id_sequences to hold the required information to generate
* up to 1,535,832,388 IDs per month. Each row requires a unique (year month)
* combination.
* see: https://github.com/jenssegers/optimus
*
* With the introduction of a new year/month, a new set of sequencer keys are
* generated.
*
* @package Services
* @author  Richard Cox <richard.cox@bluestarsports.com>
*/
class IdService
{
    /**
    * Regex to break apart ID 2017-0912-1234-1234 into its components.
    * Format: yyyy-mm99-9999-9999
    * Where:  yyyy is the year the user first registered
    *         mm is the month the user first registered
    *         99-9999-9999 is the 10 digit encoded index (hyphns should be removed)
    */
    const BREAKAPART_REGEX = "/^([0-9]{4})-([0-9]{2})([0-9]{2}-[0-9]{4}-[0-9]{4})$/";

    /**
    * Regex used to find the the 4-quartex digits ID.
    * Format: yyyy.mm9999999999
    * Where:  yyyy is the year the user first registered
    *         mm is the month the user first registered
    *         9999999999 is the 10 digit encoded index
    */
    const CONCATINATE_REGEX = "/^([0-9]{4}).([0-9]{4})([0-9]{4})([0-9]{4})$/";
    
    /**
    * Function generates a new ID using Knuth's multiplicative hashing method.
    *
    * @param year, the year the ID should be created for
    * @param month, the month the ID should be created for
    * @return array (index, uid) Where index is the index of the record
    * and the obfuscated id in the form yyyy-mm99-9999-9999
    */
    public function getNewId($year, $month)
    {
        $method = __METHOD__;

        $index = 0;
        $prime = 0;
        $inverse = 0;
        $random = 0;
        $uid = 0;

        $logContext = [
            'year' => $year,
            'month' => $month,
            'index' => $index,
            'uid' => $uid
        ];

        $record = $this->getSequenceByYearAndMonth($year, $month);
        if (!$record->isEmpty()) {
            list($year, $month, $index, $prime, $inverse, $random) = $this->updateIdSequence($record[0]);
        } else {
            list($year, $month, $index, $prime, $inverse, $random) = $this->createIdSequence($year, $month);
        }

        if ($index > 0) {
            $uid = $this->generateNewId($year, $month, $index, $prime, $inverse, $random);
        } else {
            Log::warning("{$method} Unable to create ID", $logContext);
        }

        return [$index, $uid];
    }

    /**
    * Increments sequencer index counter if a DB records exists for year and month.
    *
    * @param record, is the database record in question
    * @return array($year, $month, $index, $prime, $inverse, $random) values in
    * table row
    */
    private function updateIdSequence($record)
    {
        $year = $record->year;
        $month = $record->month;
        $index = $record->index + 1;
        $prime = $record->prime;
        $inverse = $record->inverse;
        $random = $record->random;

        $this->updateSequencerIndex($record);
        return [$year, $month, $index, $prime, $inverse, $random];
    }

    /**
    * Creates a new record if a record does not exist for year/month
    *
    * @param year
    * @param month
    * @return array($year, $month, $index, $prime, $inverse, $random) values in
    * table row.
    */
    private function createIdSequence($year, $month)
    {
        //log starting new SEQUENCER stream
        list($prime, $inverse, $random) = Energon::generate();
        $index = 1;
        $this->addSequencer($year, $month, $index, $prime, $inverse, $random);
        return [$year, $month, $index, $prime, $inverse, $random];
    }

    /**
    * Generates a new id using Knuth's multiplicative hashing method
    * with prime, inverse, and random seed data found in the database
    *
    * @param year, the year the ID is created for
    * @param month, the month the ID is created for
    * @param index, the index to encode for the ID
    * @param prime, the prime number used in optimus
    * @param inverse, the inverse where (prime * inverse) & maxid = 1;
    * @param random, a random number
    * @return uid formated yyyy-mm99-9999-9999
    */
    private function generateNewId($year, $month, $index, $prime, $inverse, $random)
    {
        $optimus = new Optimus($prime, $inverse, $random);
        $encoded = $optimus->encode($index);
        $value = sprintf("%04d-%02d%010d", $year, $month, $encoded);
        $ret = preg_replace(self::CONCATINATE_REGEX, "$1-$2-$3-$4", $value);
        return $ret;
    }

    /**
    * Breaks apart a ID into its components
    *
    * @param id, formatted 2017-0512-3456-7890
    * @return aray(year, nonth, hashedIndex); year=2017, month=m05, hashedIndex=1234567890
    */
    private function getIdComponents($id)
    {
        preg_match_all(self::BREAKAPART_REGEX, $id, $matches);
        return [
            (int) $matches[1][0],
            (int) $matches[2][0],
            (int) str_replace("-", "", $matches[3][0])
        ];
    }

    /**
    * Database call to get Sequencer record by year and month
    *
    * @param year, the year the ID is created for
    * @param month, the month the ID is created for
    * @return data record
    */
    public function getSequenceByYearAndMonth($year, $month)
    {
        return IdSequence::where('year', $year)->where('month', $month)->lockForUpdate()->get();
    }

    /**
    * Update sequencer record by one (increment by one)
    *
    * @param record, is the database record in question
    */
    public function updateSequencerIndex($record)
    {
        IdSequence::raw(function ($collection) use ($record) {
            return $collection->findOneAndUpdate(
                ['_id' => new \MongoDB\BSON\ObjectID($record->id)],
                ['$inc' => [ 'index' => 1 ]]
            );
        });
    }

    /**
    * Creates a new DB sequencer (record has to be unique year and month combination)
    *
    * @param year, the year the ID is created for
    * @param month, the month the ID is created for
    * @param index, the index to encode for the ID
    * @param prime, the primne number used in optimus
    * @param inverse, the inverse where (prime * inverse) & maxid = 1;
    * @param random, a random number
    */
    public function addSequencer($year, $month, $index, $prime, $inverse, $random)
    {
        IdSequence::insertGetId([
            'year' => $year,
            'month' => $month,
            'index' => $index,
            'prime' => $prime,
            'inverse' => $inverse,
            'random' => $random
        ]);
    }
}
