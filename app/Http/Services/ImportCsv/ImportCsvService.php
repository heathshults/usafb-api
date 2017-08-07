<?php
namespace App\Http\Services\ImportCsv;

use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;
use App\Models\Player;
use App\Models\Coach;
use Illuminate\Support\Facades\DB;
use App\Helpers\FunctionalHelper;

/*
    ImportCSV Service
    Service for importing csv to player
*/
class ImportCsvService
{
    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';
    const CSV_LINE_AMOUNT_PLAYER = 52;
    const CSV_LINE_AMOUNT_COACH = 31;

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

        $lineAmount = $this->getLineAmountByType($type);
        
        if (!ImportCsvUtils::isLineAsExpected($header, $lineAmount)) {
            return [
                'processed' => 0,
                'error' => 1
            ];
        }
        
        $indexMapperArray = ImportCsvUtils::columnToIndexMapper($header);
        $linesProcessed = 0;
        $errors = 0;
        while (($fileLine = fgetcsv($fd, 1000, ",")) !== false) {
            if (!ImportCsvUtils::isLineAsExpected($fileLine, $lineAmount)) {
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

                    $registrantModel->save();

                    switch ($type) {
                        case self::TYPE_PLAYER:
                            // Player Model
                            $playerModel = ImportCsvUtils::reduceKeyValueToModel(
                                ImportCsvUtils::mapRulesToArrayOfKeyValue(
                                    ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Player'),
                                    $indexMapperArray,
                                    $fileLine
                                ),
                                new Player
                            );
                            $registrantModel->player()->save($playerModel);
                            break;
                        case self::TYPE_COACH:
                            // Coach Model
                            $coachModel = ImportCsvUtils::reduceKeyValueToModel(
                                ImportCsvUtils::mapRulesToArrayOfKeyValue(
                                    ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Coach'),
                                    $indexMapperArray,
                                    $fileLine
                                ),
                                new Coach
                            );
                            $registrantModel->coach()->save($coachModel);
                            break;
                    }

                    $linesProcessed++;
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        }

        return ['processed' => $linesProcessed,
                'errors' => $errors];
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
        $rules = [
                'address' => ['rule' => $testRequired,
                    'field_name' => 'address_first_line',
                    'tables' => ['App\Models\Registrant']],
                'address_line_2' => ['rule' => $identity,
                    'field_name' => 'address_second_line',
                    'tables' => ['App\Models\Registrant']],
                'cell_phone' => ['rule' => $testRequired,
                    'field_name' => 'phone_number',
                    'tables' => [ 'App\Models\Registrant']],
                'city' => ['rule' => $testRequired,
                    'field_name' => 'city',
                    'tables' => ['App\Models\Registrant']],
                'county' => ['rule' => $testRequired,
                    'field_name' => 'county',
                    'tables' => ['App\Models\Registrant']],
                'date_of_birth' => ['rule' => FunctionalHelper::compose($testRequired, $parseToDate),
                    'field_name' => 'birth_date',
                    'tables' => ['App\Models\Registrant']],
                'email' => ['rule' => $testRequired,
                    'field_name' => 'email',
                    'tables' => ['App\Models\Registrant']],
                'first_name' => ['rule' => $testRequired,
                    'field_name' => 'first_name',
                    'tables' => ['App\Models\Registrant']] ,
                'game_type' =>  ['rule' => $testRequired,
                    'field_name' => 'game_type',
                    'tables' => ['App\Models\Registrant']],
                'gender' => ['rule' => $testRequired,
                    'field_name' => 'gender',
                    'tables' => ['App\Models\Registrant']],
                'last_name' => ['rule' => $testRequired,
                    'field_name' => 'last_name',
                    'tables' => ['App\Models\Registrant']],
                'level_of_play'=> ['rule' => $testRequired,
                    'field_name' => 'level_of_play',
                    'tables' => ['App\Models\Registrant']],
                'middle_name' => ['rule' => $identity,
                    'field_name' => 'middle_name',
                    'tables' => ['App\Models\Registrant']],
                'usadfb_id' => ['rule' => $testNotRequired ,
                    'field_name' => 'usadfb_id',
                    'tables' => ['App\Models\Registrant', 'App\Models\Player']],
                'state' => ['rule' => $testRequired,
                    'field_name' => 'state',
                    'tables' => [ 'App\Models\Registrant']],
                'zip' => ['rule' => $testRequired,
                    'field_name' => 'zip_code',
                    'tables' => ['App\Models\Registrant']],
                'current_grade' => ['rule' => $testRequired,
                    'field_name' => 'grade',
                    'tables' => ['App\Models\Player']],
                'height' => ['rule' => $testRequired,
                    'field_name' => 'height',
                    'tables' => ['App\Models\Player']],
                'high_school_grad_year' => ['rule' => $testRequired,
                    'field_name' => 'graduation_year',
                    'tables' => ['App\Models\Player']],
                'instagram_handle' => ['rule' => $identity,
                    'field_name' => 'instagram',
                    'tables' => ['App\Models\Player']],
                'other_sports_played' => ['rule' => $testRequired,
                    'field_name' => 'sports',
                    'tables' => ['App\Models\Player']],
                'twitter_handle' => ['rule' => $identity,
                    'field_name' => 'twitter',
                    'tables' => ['App\Models\Player']],
                'weight' => ['rule' => $testRequired,
                    'field_name' => 'weight',
                    'tables' => ['App\Models\Player']],
                '#_years_in_sport' => ['rule' => $testRequired,
                    'field_name' => 'years_at_sport',
                    'tables' => ['App\Models\Player']],
                '#_of_years_coaching' => ['rule' => $testRequired,
                    'field_name' => 'years_of_experience',
                    'tables' => ['App\Models\Coach']],
                'certifications' => ['rule' => $testRequired,
                    'field_name' => 'certifications',
                    'tables' => ['App\Models\Coach']],
                'coach_role' => ['rule' => $testRequired,
                    'field_name' => 'roles',
                    'tables' => ['App\Models\Coach']]
            ];
            return $rules;
    }

    /**
    * Returns the expected csv line Amount based on the import type
    * @return integer line Amount
    */
    public function getLineAmountByType($type)
    {
        $lineAmount = 0;

        switch ($type) {
            case self::TYPE_PLAYER:
                $lineAmount = self::CSV_LINE_AMOUNT_PLAYER;
                break;
            case self::TYPE_COACH:
                $lineAmount = self::CSV_LINE_AMOUNT_COACH;
                break;
        }

        return $lineAmount;
    }
}
