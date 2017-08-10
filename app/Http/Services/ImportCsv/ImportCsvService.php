<?php
namespace App\Http\Services\ImportCsv;

use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;
use App\Models\Registration;
use App\Models\Source;
use App\Models\Player;
use App\Models\PlayerRegistration;
use App\Models\Coach;
use App\Models\CoachRegistration;
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
    const CSV_NUMBER_FIELDS_PLAYER = 53;
    const CSV_NUMBER_FIELDS_COACH = 31;
    private $fileLine = null;
    private $indexMapperArray = null;

    /**
     * Will process the file and return an array with the result
     * Will show increment erros if line is not as big as expected (not the required amount of commas),
     * On parsing errors,
     * On Required fields missing
     * @param File: file to process
     * @param string $type The type of the CSV (PLAYER, COACH)
     * @param string $apiKey The source api key
     * @return array of processed rules and error
    */
    public function importCsvFile($file, $type, $apiKey = 'USFBKey')
    {
        $fd = fopen($file, 'r');
        
        $header = fgetcsv($fd);

        $lineAmount = $this->getLineAmountByType($type);
        
        if (!ImportCsvUtils::isLineAsExpected($header, $lineAmount)) {
            return [
                'processed' => 0,
                'errors' => 1
            ];
        }
        
        $this->indexMapperArray = ImportCsvUtils::columnToIndexMapper($header);
        $linesProcessed = 0;
        $errors = 0;
        while (($this->fileLine = fgetcsv($fd, 1000, ",")) !== false) {
            if (!ImportCsvUtils::isLineAsExpected($this->fileLine, $lineAmount)) {
                $errors ++;
            } else {
                try {
                    $registrantModel = $this->createRegistrantByType($type);
                    $sourceModel = Source::where('api_key', $apiKey)->first();
                    $registrationModel = $this->createRegistrationByType($type, $sourceModel, $registrantModel);

                    switch ($type) {
                        case self::TYPE_PLAYER:
                            $this->createPlayer($registrantModel);
                            $this->createPlayerRegistration($registrationModel);
                            break;
                        case self::TYPE_COACH:
                            $this->createCoach($registrantModel);
                            $this->createCoachRegistration($registrationModel);
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
     * Creates the Registrant by type
     * @param string $type The PLAYER or COACH types
     * @return Registrant
     */
    public function createRegistrantByType($type)
    {
        $registrantModel = ImportCsvUtils::reduceKeyValueToModel(
            ImportCsvUtils::mapRulesToArrayOfKeyValue(
                ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Registrant'),
                $this->indexMapperArray,
                $this->fileLine
            ),
            new Registrant
        );

        $registrantModel->type = $type;
        $registrantModel->save();

        return $registrantModel;
    }

    /**
     * Creates the Registration by type
     * @param string $type The PLAYER or COACH types
     * @param string $sourceModel The Source to associate the Registration
     * @param string $registrantModel The Registrant to associate the Registration
     * @return Registration
     */
    public function createRegistrationByType($type, $sourceModel, $registrantModel)
    {
        $registrationModel = ImportCsvUtils::reduceKeyValueToModel(
            ImportCsvUtils::mapRulesToArrayOfKeyValue(
                ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Registration'),
                $this->indexMapperArray,
                $this->fileLine
            ),
            new Registration
        );
        $registrationModel->type = $type;
        $registrationModel->source()->associate($sourceModel);
        $registrationModel->registrant()->associate($registrantModel);
        $registrationModel->save();

        return $registrationModel;
    }

    /**
     * Creates the Player
     * @param string $registrantModel The Registrant to associate the Player
     * @return Player
     */
    public function createPlayer($registrantModel)
    {
        $playerModel = ImportCsvUtils::reduceKeyValueToModel(
            ImportCsvUtils::mapRulesToArrayOfKeyValue(
                ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Player'),
                $this->indexMapperArray,
                $this->fileLine
            ),
            new Player
        );
        $registrantModel->player()->save($playerModel);

        return $playerModel;
    }

    /**
     * Creates the Coach
     * @param string $registrantModel The Registrant to associate the Coach
     * @return Coach
     */
    public function createCoach($registrantModel)
    {
        $coachModel = ImportCsvUtils::reduceKeyValueToModel(
            ImportCsvUtils::mapRulesToArrayOfKeyValue(
                ImportCsvUtils::filterModel($this->getRules(), 'App\Models\Coach'),
                $this->indexMapperArray,
                $this->fileLine
            ),
            new Coach
        );

        $registrantModel->coach()->save($coachModel);

        return $coachModel;
    }

    /**
     * Creates the player registration
     * @param string $registrationModel The Registration to associate the PlayerRegistration
     * @return PlayerRegistration
     */
    public function createPlayerRegistration($registrationModel)
    {
        $playerRegistrationModel = ImportCsvUtils::reduceKeyValueToModel(
            ImportCsvUtils::mapRulesToArrayOfKeyValue(
                ImportCsvUtils::filterModel($this->getRules(), 'App\Models\PlayerRegistration'),
                $this->indexMapperArray,
                $this->fileLine
            ),
            new PlayerRegistration
        );

        // Save relationship
        $registrationModel->playerRegistration()->save($playerRegistrationModel);

        return $playerRegistrationModel;
    }

    /**
     * Creates the coach registration
     * @param string $registrationModel The Registration to associate the CoachRegistration
     * @return CoachRegistration
     */
    public function createCoachRegistration($registrationModel)
    {
        $coachRegistrationModel = ImportCsvUtils::reduceKeyValueToModel(
            ImportCsvUtils::mapRulesToArrayOfKeyValue(
                ImportCsvUtils::filterModel($this->getRules(), 'App\Models\CoachRegistration'),
                $this->indexMapperArray,
                $this->fileLine
            ),
            new CoachRegistration
        );

        // Save relationship
        $registrationModel->coachRegistration()->save($coachRegistrationModel);

        return $coachRegistrationModel;
    }

    /**
    * Returns rules to process a Registrent row
    * @return array of rules
    */
    public function getRules()
    {
        $testRequired = ImportCsvUtils::toClojure('testRequired');
        $parseToDate = ImportCsvUtils::toClojure('parseToDate');
        $parseToBoolean = ImportCsvUtils::toClojure('parseToBoolean');
        $testNotRequired = ImportCsvUtils::toClojure('testNotRequired');
        $identity = FunctionalHelper::toClojure('App\Helpers\FunctionalHelper', 'identity');
        $rules = [
                'address' => ['rule' => $testRequired,
                    'field_name' => 'address_first_line',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'address_line_2' => ['rule' => $identity,
                    'field_name' => 'address_second_line',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'cell_phone' => ['rule' => $testRequired,
                    'field_name' => 'phone_number',
                    'tables' => [ 'App\Models\Registrant', 'App\Models\Registration']],
                'city' => ['rule' => $testRequired,
                    'field_name' => 'city',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'county' => ['rule' => $testRequired,
                    'field_name' => 'county',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'date_of_birth' => ['rule' => FunctionalHelper::compose($testRequired, $parseToDate),
                    'field_name' => 'birth_date',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'email' => ['rule' => $testRequired,
                    'field_name' => 'email',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'first_name' => ['rule' => $testRequired,
                    'field_name' => 'first_name',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']] ,
                'game_type' =>  ['rule' => $testRequired,
                    'field_name' => 'game_type',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'gender' => ['rule' => $testRequired,
                    'field_name' => 'gender',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'last_name' => ['rule' => $testRequired,
                    'field_name' => 'last_name',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'level_of_play'=> ['rule' => $testRequired,
                    'field_name' => 'level_of_play',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'middle_name' => ['rule' => $identity,
                    'field_name' => 'middle_name',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'usafb_id' => ['rule' => $testNotRequired ,
                    'field_name' => 'usafb_id',
                    'tables' => ['App\Models\Registrant']],
                'league' => ['rule' => $testRequired ,
                    'field_name' => 'league',
                    'tables' => ['App\Models\Registration']],
                'organization' => ['rule' => $testRequired ,
                    'field_name' => 'org_name',
                    'tables' => ['App\Models\Registration']],
                'org_state' => ['rule' => $testRequired ,
                    'field_name' => 'org_state',
                    'tables' => ['App\Models\Registration']],
                'season' => ['rule' => $testRequired ,
                    'field_name' => 'season',
                    'tables' => ['App\Models\Registration']],
                'sales_force_id' => ['rule' => $identity ,
                    'field_name' => 'external_id',
                    'tables' => ['App\Models\Registration']],
                'usafb_right_to_market' => ['rule' => $parseToBoolean,
                    'field_name' => 'right_to_market',
                    'tables' => ['App\Models\Registration']],
                'team_gender' => ['rule' => $identity ,
                    'field_name' => 'team_gender',
                    'tables' => ['App\Models\Registration']],
                'team' => ['rule' => $identity ,
                    'field_name' => 'team_name',
                    'tables' => ['App\Models\Registration']],
                'school_district' => ['rule' => $identity ,
                    'field_name' => 'school_district',
                    'tables' => ['App\Models\Registration']],
                'school_state' => ['rule' => $identity ,
                    'field_name' => 'school_state',
                    'tables' => ['App\Models\Registration']],
                'state' => ['rule' => $testRequired,
                    'field_name' => 'state',
                    'tables' => [ 'App\Models\Registrant', 'App\Models\Registration']],
                'zip' => ['rule' => $testRequired,
                    'field_name' => 'zip_code',
                    'tables' => ['App\Models\Registrant', 'App\Models\Registration']],
                'current_grade' => ['rule' => $testRequired,
                    'field_name' => 'grade',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                'height' => ['rule' => $testRequired,
                    'field_name' => 'height',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                'high_school_grad_year' => ['rule' => $testRequired,
                    'field_name' => 'graduation_year',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                'instagram_handle' => ['rule' => $identity,
                    'field_name' => 'instagram',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                'other_sports_played' => ['rule' => $testRequired,
                    'field_name' => 'sports',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                'twitter_handle' => ['rule' => $identity,
                    'field_name' => 'twitter',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                'weight' => ['rule' => $testRequired,
                    'field_name' => 'weight',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                '#_years_in_sport' => ['rule' => $testRequired,
                    'field_name' => 'years_at_sport',
                    'tables' => ['App\Models\Player', 'App\Models\PlayerRegistration']],
                'position' => ['rule' => $identity,
                    'field_name' => 'positions',
                    'tables' => ['App\Models\PlayerRegistration']],
                'school_attending' => ['rule' => $identity,
                    'field_name' => 'school_name',
                    'tables' => ['App\Models\PlayerRegistration']],
                'team_age_group' => ['rule' => $identity,
                    'field_name' => 'team_age_group',
                    'tables' => ['App\Models\PlayerRegistration']],
                '#_of_years_coaching' => ['rule' => $testRequired,
                    'field_name' => 'years_of_experience',
                    'tables' => ['App\Models\Coach', 'App\Models\CoachRegistration']],
                'certifications' => ['rule' => $identity,
                    'field_name' => 'certifications',
                    'tables' => ['App\Models\Coach', 'App\Models\CoachRegistration']],
                'coach_role' => ['rule' => $testRequired,
                    'field_name' => 'roles',
                    'tables' => ['App\Models\Coach', 'App\Models\CoachRegistration']]
            ];
            return $rules;
    }

    /**
    * Returns the expected csv line Amount based on the import type
    * @param string $type The type of the CSV (PLAYER, COACH)
    * @return integer line Amount
    */
    public function getLineAmountByType($type)
    {
        $lineAmount = 0;

        switch ($type) {
            case self::TYPE_PLAYER:
                $lineAmount = self::CSV_NUMBER_FIELDS_PLAYER;
                break;
            case self::TYPE_COACH:
                $lineAmount = self::CSV_NUMBER_FIELDS_COACH;
                break;
        }

        return $lineAmount;
    }
}
