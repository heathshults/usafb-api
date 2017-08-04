<?php
namespace App\Http\Services\ImportCsv;

use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use App\Helpers\FunctionalHelper;

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
     * @return array of processed rules and error
    */
    public function importCsvFile($file, $type)
    {
        $fd = fopen($file, 'r');
        
        $header = fgetcsv($fd);
        
        if (!ImportCsvUtils::isLineAsExpected($header)) {
            return array(
                'processed' => 0,
                'error' => 1
            );
        }
        
        $indexMapperArray = ImportCsvUtils::columnToIndexMapper($header);
        $linesProcessed = 0;
        $errors = 0;
        while (($fileLine = fgetcsv($fd, 1000, ",")) !== false) {
            if (!ImportCsvUtils::isLineAsExpected($fileLine)) {
                $errors ++;
            } else {
                try {
                    // Registrant Model
                    $registrantModel = ImportCsvUtils::reduceKeyValueToModel(
                        ImportCsvUtils::mapRulesToArrayOfKeyValue(
                            ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Registrant'),
                            $indexMapperArray,
                            $fileLine
                        ),
                        new Registrant
                    );

                    $registrantModel->type = $type;

                    // Player Model
                    $playerModel = ImportCsvUtils::reduceKeyValueToModel(
                        ImportCsvUtils::mapRulesToArrayOfKeyValue(
                            ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Player'),
                            $indexMapperArray,
                            $fileLine
                        ),
                        new Player
                    );

                    // Save Models
                    $registrantModel->save();
                    $registrantModel->player()->save($playerModel);
    
                    $linesProcessed++;
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        }

        return array('processed' => $linesProcessed,
                     'errors' => $errors);
    }

    /**
    * Returns rules to process a Registrent row
    * @return array of rules
    */
    public function getRules()
    {
        $testRequired = ImportCsvUtils::toClojure('testRequired');
        $parseToDate = ImportCsvUtils::toClojure('parseToDate');
        $testNotRequired = ImportCsvUtils::toClojure('testNotRequired');
        $identity = FunctionalHelper::toClojure('App\Helpers\FunctionalHelper', 'identity');
        $rules = array(
                'address' => array('rule' => $testRequired,
                    'field_name' => 'address_first_line',
                    'tables' => array('App\Models\Registrant')),
                'address_line_2' => array('rule' => $identity,
                    'field_name' => 'address_second_line',
                    'tables' => array('App\Models\Registrant')),
                'cell_phone' => array('rule' => $testRequired,
                    'field_name' => 'phone_number',
                    'tables' => array( 'App\Models\Registrant')),
                'city' => array('rule' => $testRequired,
                    'field_name' => 'city',
                    'tables' => array('App\Models\Registrant')),
                'county' => array('rule' => $testRequired,
                    'field_name' => 'county',
                    'tables' => array('App\Models\Registrant')),
                'date_of_birth' => array('rule' => FunctionalHelper::compose($testRequired, $parseToDate),
                    'field_name' => 'birth_date',
                    'tables' => array('App\Models\Registrant')),
                'email' => array('rule' => $testRequired,
                    'field_name' => 'email',
                    'tables' => array('App\Models\Registrant')),
                'first_name' => array('rule' => $testRequired,
                    'field_name' => 'first_name',
                    'tables' => array('App\Models\Registrant')) ,
                'game_type' =>  array('rule' => $testRequired,
                    'field_name' => 'game_type',
                    'tables' => array('App\Models\Registrant')),
                'gender' => array('rule' => $testRequired,
                    'field_name' => 'gender',
                    'tables' => array('App\Models\Registrant')),
                'last_name' => array('rule' => $testRequired,
                    'field_name' => 'last_name',
                    'tables' => array('App\Models\Registrant')),
                'level_of_play'=> array('rule' => $testRequired,
                    'field_name' => 'level_of_play',
                    'tables' => array('App\Models\Registrant')),
                'middle_name' => array('rule' => $identity,
                    'field_name' => 'middle_name',
                    'tables' => array('App\Models\Registrant')),
                'usadfb_id' => array('rule' => $testNotRequired ,
                    'field_name' => 'usadfb_id',
                    'tables' => array('App\Models\Registrant', 'App\Models\Player')),
                'state' => array('rule' => $testRequired,
                    'field_name' => 'state',
                    'tables' => array( 'App\Models\Registrant')),
                'zip' => array('rule' => $testRequired,
                    'field_name' => 'zip_code',
                    'tables' => array('App\Models\Registrant')),
                'current_grade' => array('rule' => $testRequired,
                    'field_name' => 'grade',
                    'tables' => array('App\Models\Player')),
                'height' => array('rule' => $testRequired,
                    'field_name' => 'height',
                    'tables' => array('App\Models\Player')),
                'high_school_grad_year' => array('rule' => $testRequired,
                    'field_name' => 'graduation_year',
                    'tables' => array('App\Models\Player')),
                'instagram_handle' => array('rule' => $identity,
                    'field_name' => 'instagram',
                    'tables' => array('App\Models\Player')),
                'other_sports_played' => array('rule' => $testRequired,
                    'field_name' => 'sports',
                    'tables' => array('App\Models\Player')),
                'twitter_handle' => array('rule' => $identity,
                    'field_name' => 'twitter',
                    'tables' => array('App\Models\Player')),
                'weight' => array('rule' => $testRequired,
                    'field_name' => 'weight',
                    'tables' => array('App\Models\Player')),
                '#_years_in_sport' => array('rule' => $testRequired,
                    'field_name' => 'years_at_sport',
                    'tables' => array('App\Models\Player'))
            );
            return $rules;
    }
}
