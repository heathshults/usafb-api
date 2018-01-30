<?php

namespace App\Console\Commands\Warehouse;

use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;

class PlayerLoader implements LoaderInterface
{
    protected $maxStageId = 0;
        
    /**
     * Load staged Player data into Coach dimension
     *
     * @return int number of results loaded
     */
    public function load(array $options = [])
    {
        // stage_player_reg_events
        $this->maxStageId = $this->findMaxRecordId();
        $this->prepareRecords();
        // remove duplicate records in staging table
        $this->dedupeRecords();
        $this->validateRecords();
        // add records that don't already exist
        $this->addRecords();
        // update existing records
        $this->updateRecords();
        // cleanup records
        $this->removeStagedRecords();
        return true;
    }
    
    protected function findMaxRecordId()
    {
        // find max id of records in staging table
        $maxIdResult = DB::connection('mysql-dw')->selectOne('
            SELECT MAX(id) AS max_id FROM stg_player_events
        ');
        return $maxIdResult->max_id;
    }
    
    protected function prepareRecords()
    {
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_player_events`
        ');
        DB::connection('mysql-dw')->statement(
            'CREATE TABLE wrk_stg_player_events ENGINE=Innodb CHARSET=utf8 AS 
            SELECT * FROM stg_player_events WHERE id <= ?',
            [ $this->maxStageId ]
        );
        $indexes = [ 'created_at', 'player_id', 'zip', 'gender', 'dob' ];
        foreach ($indexes as $index) {
            DB::connection('mysql-dw')->statement("
                CREATE INDEX idx_${index} ON wrk_stg_player_events (${index})
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
            DROP TABLE IF EXISTS `wrk_stg_player_events_unique`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_player_events_unique ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                w.*
            FROM wrk_stg_player_events w
            JOIN (
                SELECT
                    player_id AS player_id,
                    MAX(created_at) AS created_at
                FROM
                    wrk_stg_player_events
                GROUP BY
                    player_id                
            ) max_w ON 
                w.player_id = max_w.player_id
                AND w.created_at = max_w.created_at
            GROUP BY
                w.player_id
        ');
        return true;
    }
    
    protected function validateRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_player_events_valid`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_player_events_valid ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                event_id,
                event_created_at,
                player_id,
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
                dim_player_id,
                dim_location_id,
                dim_gender_id
            FROM (
                SELECT
                    w.id AS event_id,
                    w.created_at AS event_created_at,
                    w.player_id AS player_id,
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
                    dim_players.id AS dim_player_id,
                    dim_locations.id AS dim_location_id,
                    dim_genders.id AS dim_gender_id
                FROM wrk_stg_player_events_unique w
                LEFT JOIN dim_players ON 
                    dim_players.player_id = w.player_id
                LEFT JOIN dim_locations ON
                    dim_locations.zip = w.zip
                LEFT JOIN dim_genders ON
                    dim_genders.gender = w.gender
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
            DROP TABLE IF EXISTS `wrk_stg_player_events_add`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_player_events_add ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                w.*
            FROM 
                wrk_stg_player_events_valid w
            WHERE
                w.dim_player_id IS NULL
        ');
        DB::connection('mysql-dw')->statement('
            INSERT INTO dim_players (
                player_id,
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
                player_id,
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
                wrk_stg_player_events_add
        ');
        return true;
    }
    
    protected function updateRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_player_events_update`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_player_events_update ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                w.*
            FROM 
                wrk_stg_player_events_valid w
            WHERE
                w.dim_player_id IS NOT NULL
        ');
        DB::connection('mysql-dw')->statement('
            UPDATE 
                dim_players dp,
                wrk_stg_player_events_update w
            SET
                dp.id_usafb = w.id_usafb,
                dp.id_external = w.id_external,
                dp.name_first = w.name_first,
                dp.name_middle = w.name_middle,
                dp.name_last = w.name_last,
                dp.dob = w.dob,
                dp.years_experience = w.years_experience,
                dp.dim_location_id = w.dim_location_id,
                dp.dim_gender_id = w.dim_gender_id,
                dp.updated_at = NOW()
            WHERE
                dp.player_id = w.player_id
                AND
                dp.id = w.dim_player_id
        ');
        return true;
    }
    
    protected function removeStagedRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement(
            'DELETE FROM stg_player_events WHERE id <= ?',
            [ $this->maxStageId ]
        );
        return true;
    }
}
