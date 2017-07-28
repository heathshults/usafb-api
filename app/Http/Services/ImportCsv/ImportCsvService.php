<?php
namespace App\Http\Services\ImportCsv;

use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;
use Illuminate\Support\Facades\DB;

/*
    ImportCSV Service
    Service for importing csv to player
*/
class ImportCsvService
{
    
    /**
     * Will process the file and return an array with the result
     * Will show increment erros if line is not as big as expected (not the required amount of commas),
     * On parsing errors,
     * On Required fields missing
     * @param File: file to process
     * @returns array(
     *  processed ::Integer, ; Amount of items processed and inserted successfully
     *  errors ::Integer ; Amount of errors found
     * );
    */
    public function importCsvFile($file, $type)
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
                if ($this->processLine($line_of_text, $type)) {
                    $linesProcessed++;
                } else {
                    $errors++;
                }
            }
        }

        return array('processed' => $linesProcessed,
                     'errors' => $errors);
    }

    /**
     *  Returns a bollean to show if the line was procesed correctly
     *  @param array representing csv line
     *  @return bool if line was processed successfully
    */
    private function processLine(array $line_of_text, $type)
    {
        $success = false;
        try {
            $lineItem = array(
                'type' =>
                    array('value' => ImportCsvUtils::testRequired($type),
                    'tables' => array('App\Models\Registrant')),
                'sport_years' =>
                    array('value' => $line_of_text[0]),
                'address_first_line' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[1]), 'tables' =>
                        array('App\Models\Registrant')),
                'birth_certificate' =>
                    array('value' => $line_of_text[2]),
                'phone_number' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[3]), 'tables' =>
                        array( 'App\Models\Registrant')),
                'city' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[4]), 'tables' =>
                        array('App\Models\Registrant')),
                'county' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[5]), 'tables' =>
                        array('App\Models\Registrant')),
                'grade' =>
                    array('value' => $line_of_text[6]),
                'birth_date' =>
                    array('value' => ImportCsvUtils::parseToDate(
                        ImportCsvUtils::testRequired($line_of_text[7])
                    ), 'tables' => array('App\Models\Registrant')),
                'email' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[8]), 'tables' =>
                        array('App\Models\Registrant')),
                'first_name' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[9]), 'tables' =>
                        array('App\Models\Registrant')) ,
                'game_type' =>  array('value' => ImportCsvUtils::testRequired($line_of_text[10]), 'tables' =>
                        array('App\Models\Registrant')),
                'gender'=>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[11]), 'tables' =>
                        array('App\Models\Registrant')),
                'height' =>
                    array('value' => $line_of_text[12]),
                'graduation_year' =>
                    array('value' => $line_of_text[13]),
                'instagram_handle' =>
                    array('value' => $line_of_text[14]),
                'last_name' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[15]), 'tables' =>
                        array('App\Models\Registrant')),
                'league' =>
                    array('value' => $line_of_text[16]),
                'level_of_play'=> array('value' => ImportCsvUtils::testRequired($line_of_text[17]), 'tables' =>
                        array('App\Models\Registrant')),
                'middle_name' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[18]), 'tables' =>
                        array('App\Models\Registrant')),
                'org_name' =>
                    array('value' => $line_of_text[19]),
                'org_state' =>
                    array('value' => $line_of_text[20]),
                'sports' =>
                    array('value' => $line_of_text[21]),
                'guardian_1_cell' =>
                    array('value' =>  $line_of_text[22]),
                'guardian_1_email' =>
                    array('value' =>  $line_of_text[23]),
                'guardian_1_first_name' =>
                    array('value' =>  $line_of_text[24]),
                'guardian_1_home_phone' =>
                    array('value' =>  $line_of_text[25]),
                'guardian_1_last_name' =>
                    array('value' =>  $line_of_text[26]),
                'guardian_1_work_phone' =>
                    array('value' =>  $line_of_text[27]),
                'guardian_2_cell' =>
                    array('value' =>  $line_of_text[28]),
                'guardian_2_email' =>
                    array('value' =>  $line_of_text[29]),
                'guardian_2_first_name' =>
                    array('value' =>  $line_of_text[30]),
                'guardian_2_home_phone' =>
                    array('value' =>  $line_of_text[31]),
                'guardian_2_last_name' =>
                    array('value' =>  $line_of_text[32]),
                'guardian_2_work_phone' =>
                    array('value' =>  $line_of_text[32]),
                'photo' =>
                    array('value' =>  $line_of_text[34]),
                'salesforce_id' =>
                    array('value' => $line_of_text[35]),
                'usadfb_id' => array('value' => ImportCsvUtils::testNotRequired($line_of_text[36])),
                'positions' =>
                    array('value' => $line_of_text[37]),
                'player_last_updated' =>
                    array('value' => $line_of_text[38]),
                'school' =>
                    array('value' => $line_of_text[39]),
                'school_district' =>
                    array('value' => $line_of_text[40]),
                'school_state' =>
                    array('value' => $line_of_text[41]),
                'season' =>
                    array('value' => $line_of_text[42]),
                'state' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[43]), 'tables' =>
                        array( 'App\Models\Registrant')),
                'team_name' =>
                    array('value' => $line_of_text[44]),
                'team_age_group' =>
                    array('value' => $line_of_text[45]),
                'team_gender' =>
                    array('value' => $line_of_text[46]),
                'twitter_handle' =>
                    array('value' => $line_of_text[47]),
                'usafb_market' =>
                    array('value' => $line_of_text[48]),
                'weight' =>
                    array('value' => $line_of_text[49]),
                'zip_code' =>
                    array('value' => ImportCsvUtils::testRequired($line_of_text[50]), 'tables' =>
                        array('App\Models\Registrant'))
            );
            $model = ImportCsvUtils::reduceRulesToModel(
                ImportCsvUtils::filterModel($lineItem, 'App\Models\Registrant'),
                new Registrant
            );
       
            $model->save();
            $success = true;
        } catch (\Exception $e) {
            $success = false;
        }
        return $success;
    }
}
