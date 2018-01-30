<?php

namespace App\Console\Commands\Warehouse;

use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;

class CoachLoader implements LoaderInterface
{
    protected $maxStageId = 0;
        
    /**
     * Load staged Coach data into Coach dimension
     *
     * @return int number of results loaded
     */
    public function load(array $options = [])
    {
        $this->maxStageId = $this->findMaxRecordId();
        $this->prepareRecords();
        // remove duplicate records in staging table
        $this->dedupeRecords();
        $this->validateRecords();
        // add records that don't already exist
        $this->addRecords();
        // update existing records
        $this->updateRecords();
        // remove records
        $this->removeStagedRecords();
        return true;
    }
    
    protected function findMaxRecordId()
    {
        // find max id of records in staging table
        $maxIdResult = DB::connection('mysql-dw')->selectOne('
            SELECT MAX(id) AS max_id FROM stg_coach_events
        ');
        return $maxIdResult->max_id;
    }

    protected function prepareRecords()
    {
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_coach_events`
        ');
        DB::connection('mysql-dw')->statement(
            'CREATE TABLE wrk_stg_coach_events ENGINE=Innodb CHARSET=utf8 AS 
            SELECT * FROM stg_coach_events WHERE id <= ?',
            [ $this->maxStageId ]
        );
        $indexes = [ 'created_at', 'coach_id', 'zip', 'gender', 'dob' ];
        foreach ($indexes as $index) {
            DB::connection('mysql-dw')->statement("
                CREATE INDEX idx_${index} ON wrk_stg_coach_events (${index})
            ");
        }
        return true;
    }
    
    protected function dedupeRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_coach_events_unique`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_coach_events_unique ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                w.*
            FROM wrk_stg_coach_events w
            JOIN (
                SELECT
                    coach_id AS coach_id,
                    MAX(created_at) AS created_at
                FROM
                    wrk_stg_coach_events
                GROUP BY
                    coach_id                
            ) max_w ON 
                w.coach_id = max_w.coach_id
                AND w.created_at = max_w.created_at
            GROUP BY
                w.coach_id
        ');
        return true;
    }

    protected function validateRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_coach_events_valid`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_coach_events_valid ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                event_id,
                event_created_at,
                coach_id,
                id_usafb,
                id_external,
                name_first,
                name_middle,
                name_last,
                dob,
                gender,
                zip,
                years_experience,
                opt_in_marketing,
                created_date,
                updated_date,
                dim_coach_id,
                dim_location_id,
                dim_gender_id
            FROM (
                SELECT
                    w.id AS event_id,
                    w.created_at AS event_created_at,
                    w.coach_id AS coach_id,
                    w.id_usafb AS id_usafb,
                    w.id_external AS id_external,
                    TRIM(w.name_first) AS name_first,
                    TRIM(w.name_middle) AS name_middle,
                    TRIM(w.name_last) AS name_last,
                    w.dob AS dob,
                    w.gender AS gender,
                    w.zip AS zip,
                    w.years_experience AS years_experience,
                    w.opt_in_marketing AS opt_in_marketing,
                    w.created_date AS created_date,
                    w.updated_date AS updated_date,
                    dc.id AS dim_coach_id,
                    dl.id AS dim_location_id,
                    dg.id AS dim_gender_id
                FROM wrk_stg_coach_events_unique w
                LEFT JOIN dim_coaches dc ON 
                    dc.coach_id = w.coach_id
                LEFT JOIN dim_locations dl ON
                    dl.zip = w.zip
                LEFT JOIN dim_genders dg ON
                    dg.gender = w.gender
            ) AS validation
        ');
        return true;
    }
    
    protected function addRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_coach_events_add`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_coach_events_add ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                w.*
            FROM 
                wrk_stg_coach_events_valid w
            WHERE
                w.dim_coach_id IS NULL
        ');
        DB::connection('mysql-dw')->statement('
            INSERT INTO dim_coaches (
                coach_id,
                id_usafb,
                id_external,
                name_first,
                name_middle,
                name_last,
                dob,
                years_experience,
                dim_location_id,
                dim_gender_id,
                created_at
            ) SELECT
                coach_id,
                id_usafb,
                id_external,
                name_first,
                name_middle,
                name_last,
                dob,
                years_experience,
                dim_location_id,
                dim_gender_id,
                NOW()
            FROM
                wrk_stg_coach_events_add
        ');
        return true;
    }
    
    protected function updateRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_coach_events_update`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_coach_events_update ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                w.*
            FROM 
                wrk_stg_coach_events_valid w
            WHERE
                w.dim_coach_id IS NOT NULL
        ');
        DB::connection('mysql-dw')->statement('
            UPDATE 
                dim_coaches dc,
                wrk_stg_coach_events_update w
            SET
                dc.id_usafb = w.id_usafb,
                dc.id_external = w.id_external,
                dc.name_first = w.name_first,
                dc.name_middle = w.name_middle,
                dc.name_last = w.name_last,
                dc.dob = w.dob,
                dc.years_experience = w.years_experience,
                dc.dim_location_id = w.dim_location_id,
                dc.dim_gender_id = w.dim_gender_id,
                dc.updated_at = NOW()
            WHERE
                dc.coach_id = w.coach_id
                AND
                dc.id = w.dim_coach_id
        ');
        return true;
    }
    
    protected function removeStagedRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement(
            'DELETE FROM stg_coach_events WHERE id <= ?',
            [ $this->maxStageId ]
        );
        return true;
    }
}
