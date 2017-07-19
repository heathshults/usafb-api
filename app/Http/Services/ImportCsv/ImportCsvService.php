<?php
namespace App\Http\Services\ImportCsv;

use App\Http\Services\ImportCsv\InserterBuilder;
use App\Models\Player;
use App\Models\PlayerTeam;
use App\Models\GameType;
use App\Models\PlayerLevel;
use Illuminate\Support\Facades\DB;

/*
    ImportCSV Service
    Service for importing csv to player
*/
class ImportCsvService
{
    
    /*
     Will process the file and return an array with the result
     Will show increment erros if line is not as big as expected (not the required amount of commas),
     On parsing errors,
     On Required fields missing
     @param File: file to process
     @returns array(
       processed ::Integer, ; Amount of items processed and inserted successfully
       errors ::Integer ; Amount of errors found
     );
    */
    public function importCsvFile($file)
    {
        $fd = fopen($file, 'r');
        // Discard first line
        $line_of_text = fgetcsv($fd);
        $linesProcessed = 0;
        $errors = 0;
        while (!feof($fd)) {
             $line_of_text = fgetcsv($fd);
            if (!is_array($line_of_text)) {
                $errors ++;
            } elseif (sizeof($line_of_text) != 51) {
                $errors++;
            } else {
                if ($this->processLine($line_of_text)) {
                    $linesProcessed++;
                } else {
                    $errors++;
                }
            }
        }

        return array('processed' => $linesProcessed,
                     'errors' => $errors);
    }

    /*
       Returns a bollean to show if the line was procesed correctly
       @param array representing csv line
    */
    private function processLine(array $line_of_text)
    {
        $lineItem = array(
            'sport_years' =>
                array('value' => $line_of_text[0], 'type' => 'int', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'address_first_line' =>
                array('value' => $line_of_text[1], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'birth_certificate' =>
                array('value' => $line_of_text[2], 'type' => 'bool?', 'tables' =>
                    array('App\Models\PlayerRegistration')),
            'phone' =>
                array('value' => $line_of_text[3], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'city' =>
                array('value' => $line_of_text[4], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'country' =>
                array('value' => $line_of_text[5], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'grade' =>
                array('value' => $line_of_text[6], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'birth_date' =>
                array('value' => $line_of_text[7], 'type' => 'date', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'email' =>
                array('value' => $line_of_text[8], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'first_name' =>
                array('value' => $line_of_text[9], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')) ,
            'game_type' => $line_of_text[10],
            'gender'=>
                array('value' => $line_of_text[11], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'height' =>
                array('value' => $line_of_text[12], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'graduation_year' =>
                array('value' => $line_of_text[13], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'instagram_handle' =>
                array('value' => $line_of_text[14], 'type' => 'int?', 'tables' =>
                    array( 'App\Models\PlayerRegistration')),
            'last_name' =>
                array('value' => $line_of_text[15], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'league' =>
                array('value' => $line_of_text[16], 'type' => 'string', 'tables' =>
                    array( 'App\Models\PlayerRegistration')),
            'level'=> $line_of_text[17],
            'middle_name' =>
                array('value' => $line_of_text[18], 'type' => 'string', 'tables' =>
                    array('App\Models\Player','App\Models\PlayerRegistration')),
            'org_name' =>
                array('value' => $line_of_text[19], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerRegistration')),
            'org_state' =>
                array('value' => $line_of_text[20], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerRegistration')),
            'sports' =>
                array('value' => $line_of_text[21], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'guardian_1_cell' =>
                array('value' =>  $line_of_text[22], 'attr_name' => 'cell_phone', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_1_email' =>
                array('value' =>  $line_of_text[23], 'attr_name' => 'email', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_1_first_name' =>
                array('value' =>  $line_of_text[24], 'attr_name' => 'first_name', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_1_home_phone' =>
                array('value' =>  $line_of_text[25], 'attr_name' => 'home_phone', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_1_last_name' =>
                array('value' =>  $line_of_text[26], 'attr_name' => 'last_name', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_1_work_phone' =>
                array('value' =>  $line_of_text[27], 'attr_name' => 'work_phone', 'type' => 'string?', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_2_cell' =>
                array('value' =>  $line_of_text[28], 'attr_name' => 'cell_phone', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_2_email' =>
                array('value' =>  $line_of_text[29], 'attr_name' => 'email', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_2_first_name' =>
                array('value' =>  $line_of_text[30], 'attr_name' => 'first_name', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_2_home_phone' =>
                array('value' =>  $line_of_text[31], 'attr_name' => 'home_phone', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_2_last_name' =>
                array('value' =>  $line_of_text[32], 'attr_name' => 'last_name', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'guardian_2_work_phone' =>
                array('value' =>  $line_of_text[32], 'attr_name' => 'work_phone', 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerParent')),
            'photo' =>
                array('value' =>  $line_of_text[34], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerRegistration')),
            'salesforce_id' =>
                array('value' => $line_of_text[35], 'type' => 'string', 'tables' =>
                    array('App\Models\Player')),
            'usadfb_id' => $line_of_text[36],
            'positions' =>
                array('value' => $line_of_text[37], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'player_last_updated' =>
                array('value' => $line_of_text[38], 'type' => 'date', 'tables' =>
                    array('App\Models\Player')),
            'school' =>
                array('value' => $line_of_text[39], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerTeam', 'App\Models\PlayerRegistration')),
            'school_district' =>
                array('value' => $line_of_text[40], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerRegistration')),
            'school_state' =>
                array('value' => $line_of_text[41], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerTeam', 'App\Models\PlayerRegistration')),
            'season' =>
                array('value' => $line_of_text[42], 'type' => 'string', 'tables' =>
                    array( 'App\Models\PlayerRegistration')),
            'state' =>
                array('value' => $line_of_text[43], 'type' => 'string', 'tables' =>
                    array( 'App\Models\PlayerRegistration')),
            'team_name' =>
                array('value' => $line_of_text[44], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerTeam', 'App\Models\PlayerRegistration')),
            'team_age_group' =>
                array('value' => $line_of_text[45], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerTeam', 'App\Models\PlayerRegistration')),
            'team_gender' =>
                array('value' => $line_of_text[46], 'type' => 'string', 'tables' =>
                    array('App\Models\PlayerTeam', 'App\Models\PlayerRegistration')),
            'twitter_handle' =>
                array('value' => $line_of_text[47], 'type' => 'int?', 'tables' =>
                    array('App\Models\PlayerRegistration')),
            'usafb_market' =>
                array('value' => $line_of_text[48], 'type' => 'bool?', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'weight' =>
                array('value' => $line_of_text[49], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration')),
            'zip_code' =>
                array('value' => $line_of_text[50], 'type' => 'string', 'tables' =>
                    array('App\Models\Player', 'App\Models\PlayerRegistration'))
        );
            
        $success = false;
        try {
            DB::beginTransaction();
            $usafbId = function ($v) use ($lineItem) {
                if (is_null($lineItem['usadfb_id']) || trim($lineItem['usadfb_id']) == '') {
                    // TODO choose a better hashing algorithm
                    $maxPlayerId = DB::table('player')
                        ->find(DB::table('player')->max('id'));
                    $newSequence = is_null($maxPlayerId) ? 0 : $maxPlayerId->id + 1;
                    $uniqueId = date('Y', time()).str_pad($newSequence, 16, '0', STR_PAD_LEFT);

                    $v->setAttribute('usadfb_id', $uniqueId);
                } else {
                    throw  new \Exception('usadfb_id is present expected empty will fail');
                }
                return $v;
            };
            
            $player = InserterBuilder::createInserterForClass('App\Models\Player')
                        ->usingHashTable($lineItem)
                        ->applyingBeforeSaveHook($usafbId)
                        ->buildAndSave();
            
            $playerToPlayerTeam = function ($pt) use ($player) {
                $pt->setAttribute('player_id', $player->id);
                return $pt;
            };
            
            $playerTeam = InserterBuilder::createInserterForClass('App\Models\PlayerTeam')
                            ->usingHashTable($lineItem)
                            ->applyingBeforeSaveHook($playerToPlayerTeam)
                            ->buildAndSave();

            $gameType = GameType::where('name', strtoupper($lineItem['game_type']))->first();
            $playerLevel = PlayerLevel::where('name', strtoupper($lineItem['level']))->first();
            
            $playerRegistrationExternalIds = function ($pr) use ($player, $gameType, $playerLevel) {
                $pr->setAttribute('player_id', $player->id);
                $pr->setAttribute('game_type_id', $gameType->id);
                $pr->setAttribute('level_id', $gameType->id);
                return $pr;
            };

            $playerRegistration = InserterBuilder::createInserterForClass('App\Models\PlayerRegistration')
                                    ->usingHashTable($lineItem)
                                    ->applyingBeforeSaveHook($playerRegistrationExternalIds)
                                    ->buildAndSave();

            $partitionCondition = function ($fieldMetaKey, $fieldMetaValue) {
                return strpos($fieldMetaKey, '1') !== false; // if the key contains the number one
            };

            $playerParentExternalId = function ($pp) use ($playerRegistration) {
                $pp->setAttribute('player_registration_id', $playerRegistration->id);
                return $pp;
            };

            $playerParentBuilderGenerator = InserterBuilder::createInserterForClass('App\Models\PlayerParent')
                                                ->usingHashTable($lineItem)
                                                ->applyingBeforeSaveHook($playerParentExternalId)
                                                ->partitionBy($partitionCondition);

            foreach ($playerParentBuilderGenerator as $value) {
                $value->buildAndSave();
            }
            DB::commit();
            $success = true;
        } catch (\Exception $e) {
            $success = false;
            DB::rollback();
        }
        return $success;
    }
}
