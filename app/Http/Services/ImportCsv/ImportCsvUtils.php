<?php

namespace App\Http\Services\ImportCsv;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\FunctionalHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ImportCsvUtils
{
    const DATE_FORMAT = 'Y-m-d';
    const EXPECTED_LINE_AMOUNT = 52;
    const RULE_IDX = 'rule';
    const FIELD_NAME_IDX = 'field_name';
    const CSV_TABLE_MAPPING = [
      'address' => 'address_first_line',
      'address_line_2' => 'address_second_line',
      'cell_phone' => 'phone_number',
      'date_of_birth' => 'birth_date',
      'zip' => 'zip_code',
      'current_grade' => 'grade',
      'high_school_grad_year' => 'graduation_year',
      'instagram_handle' => 'instagram',
      'other_sports_played' => 'sports',
      'twitter_handle' => 'twitter',
      '#_years_in_sport' => 'years_at_sport',
      '#_of_years_coaching' => 'years_of_experience',
      'coach_role' => 'roles',
      'city' => 'city',
      'country' => 'country',
      'email' => 'email',
      'first_name' => 'first_name',
      'game_type' => 'game_type',
      'gender' => 'gender',
      'last_name' => 'last_name',
      'level' => 'level',
      'middle_name' => 'middle_name',
      'usafb_id' => 'usafb_id',
      'state' => 'state',
      'height' => 'height',
      'weight' => 'weight',
      'team_age_group' => 'team_age_group',
      'certifications' => 'certifications'
    ];

    const CSV_TABLE_MAPPING_GUARDIAN = [
      'parent_/_guardian_1_cell_phone' => 'pg_mobile_phone',
      'parent_/_guardian_1_email' => 'pg_email',
      'parent_/_guardian_1_first_name' => 'pg_first_name',
      'parent_/_guardian_1_last_name' => 'pg_last_name',
      'parent_/_guardian_1_home_phone' => 'pg_home_phone',
      'parent_/_guardian_1_work_phone' => 'pg_work_phone',
      'parent_/_guardian_2_cell_phone' => 'pg_mobile_phone',
      'parent_/_guardian_2_email' => 'pg_email',
      'parent_/_guardian_2_first_name' => 'pg_first_name',
      'parent_/_guardian_2_last_name' => 'pg_last_name',
      'parent_/_guardian_2_home_phone' => 'pg_home_phone',
      'parent_/_guardian_2_work_phone' => 'pg_work_phone'
    ];

    const CSV_TABLE_MAPPING_REGISTRATION = [
      'league' => 'league',
      'team' => 'team_name',
      'team_gender' => 'team_gender',
      'organization' => 'org_name',
      'salesforce_id' => 'external_id',
      'usafb_right_to_market' => 'right_to_market',
      'position' => 'positions',
      'school_attending' => 'school_name',
      'org_state' => 'org_state',
      'season' => 'season',
      'school_district' => 'school_district',
      'school_state' => 'school_state'
    ];

    public static function mapCsvColumnsToTableFields($row)
    {
        $row['guardians'] = [];
        $row['registrations'] = [];
        foreach ($row as $key => $value) {
            if (isset(self::CSV_TABLE_MAPPING[$key])) {
                unset($row[$key]);
                $row[self::CSV_TABLE_MAPPING[$key]] = $value;
            } elseif (isset(self::CSV_TABLE_MAPPING_GUARDIAN[$key])) {
                preg_match('/\d+/', $key, $matches);
                unset($row[$key]);
                $row['guardians'][$matches[0]-1][self::CSV_TABLE_MAPPING_GUARDIAN[$key]] = $value;
            } elseif (isset(self::CSV_TABLE_MAPPING_REGISTRATION[$key])) {
                unset($row[$key]);
                $row['registrations'][0][self::CSV_TABLE_MAPPING_REGISTRATION[$key]] = $value;
            }
        }

        return $row;
    }

    /**
     * Validate the max rows of the csv
     * @param File $file The csv file
     * @return int the number of rows
     */
    public static function countRows($file)
    {
        $fp = file($file);

        return count($fp);
    }

    /**
    * Will return an array where key is the column key and value is the index for that key
    * @param array $columnNames Array of column names
    * @return array of index and column names
    */
    public static function columnToIndexMapper(array $columnNames)
    {
        return array_map(self::toClojure('lowerCaseAndSpacesToUnderscore'), $columnNames);
    }

    /**
    * Will return true if line is an array and has expected length false otherwise
    * @param mixed $line will be tested for array an length
    * @param number expected column lenght
    * @return bool
    */
    public static function isLineAsExpected($line, $expectedLineAmount = self::EXPECTED_LINE_AMOUNT)
    {
        return is_array($line) && (sizeof($line) == $expectedLineAmount);
    }

    /**
    * Will return the anonimous function of passed method name
    * @param string $methodName method name within this class
    * @return anonimous function
    */
    public static function toClojure($methodName)
    {

        return FunctionalHelper::toClojure('App\Http\Services\ImportCsv\ImportCsvUtils', $methodName);
    }
    /**
    * Composes two functions. will lowercase $value and replace spaces for underscores
    * @param string $value value to convert
    * @return new value without spaces and lower cased
    */
    public static function lowerCaseAndSpacesToUnderscore($value)
    {
        return str_replace(' ', '_', strtolower($value));
    }
}
