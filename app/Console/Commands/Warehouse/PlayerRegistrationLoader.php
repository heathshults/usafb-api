<?php

namespace App\Console\Commands\Warehouse;

use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;

class PlayerRegistrationLoader implements LoaderInterface
{
    protected $maxStageId = 0;
        
    /**
     * Load staged Player Registration data/records into Player Registration cube
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
            SELECT MAX(id) AS max_id FROM stg_player_reg_events
        ');
        return $maxIdResult->max_id;
    }

    protected function prepareRecords()
    {
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_player_reg_events`
        ');
        DB::connection('mysql-dw')->statement(
            'CREATE TABLE wrk_stg_player_reg_events ENGINE=Innodb CHARSET=utf8 AS 
            SELECT * FROM stg_player_reg_events WHERE id <= ?',
            [ $this->maxStageId ]
        );
        $indexes = [
            'created_at',
            'registration_id',
            'player_id',
            'registration_date',
            'level',
            'level_type',
            'position'
        ];
        foreach ($indexes as $index) {
            DB::connection('mysql-dw')->statement("
                CREATE INDEX idx_${index} ON wrk_stg_player_reg_events (${index})
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
            DROP TABLE IF EXISTS `wrk_stg_player_reg_events_unique`
        ');
        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_player_reg_events_unique ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                w.*
            FROM wrk_stg_player_reg_events w
            JOIN (
                SELECT
                    registration_id AS registration_id,
                    MAX(created_at) AS created_at
                FROM
                    wrk_stg_player_reg_events
                GROUP BY
                    registration_id                
            ) max_w ON 
                w.registration_id = max_w.registration_id
                AND w.created_at = max_w.created_at
            GROUP BY
                w.registration_id
        ');
        return true;
    }

    protected function validateRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            DROP TABLE IF EXISTS `wrk_stg_player_reg_events_valid`
        ');

        DB::connection('mysql-dw')->statement('
            CREATE TABLE wrk_stg_player_reg_events_valid ENGINE=Innodb CHARSET=utf8 AS
            SELECT
                event_id,
                event_created_at,
                registration_id,
                player_id,
                registration_date,
                level,
                level_type,
                position,
                organization_name,
                organization_state,
                league_name,
                season_year,
                season,                
                school_name,
                school_district,
                school_state,
                dim_date_id,
                dim_level_id,
                dim_level_type_id,
                dim_position_id,
                dim_player_id,
                (invalid_1 + invalid_2 + invalid_3) AS invalid
            FROM (
                SELECT
                    w.id AS event_id,
                    w.created_at AS event_created_at,
                    w.registration_id AS registration_id,
                    w.player_id AS player_id,
                    w.registration_date AS registration_date,
                    w.level AS level,
                    w.level_type AS level_type,
                    w.position AS position,
                    TRIM(w.organization_name) AS organization_name,
                    TRIM(w.organization_state) AS organization_state,
                    TRIM(w.league_name) AS league_name,
                    w.season_year AS season_year,
                    TRIM(w.season) AS season,
                    TRIM(w.school_name) AS school_name,
                    TRIM(w.school_district) AS school_district,
                    TRIM(w.school_state) AS school_state,
                    dim_players.id AS dim_player_id,
                    dim_dates.id AS dim_date_id,
                    dim_levels.id As dim_level_id,
                    dim_level_types.id AS dim_level_type_id,
                    dim_positions.id AS dim_position_id,
                    IF(w.registration_id IS NULL, 1, 0) AS invalid_1,
                    IF(dim_players.id IS NULL, 1, 0) AS invalid_2,
                    IF(dim_dates.id IS NULL, 1, 0) AS invalid_3
                FROM wrk_stg_player_reg_events_unique w
                LEFT JOIN dim_players ON 
                    dim_players.player_id = w.player_id
                LEFT JOIN dim_dates ON
                    dim_dates.calendar_date = w.registration_date
                LEFT JOIN dim_levels ON
                    dim_levels.level = w.level
                LEFT JOIN dim_level_types ON
                    dim_level_types.level_type = w.level_type
                LEFT JOIN dim_positions ON
                    (dim_positions.position_type = "players" AND dim_positions.position = w.position)
            ) AS validation
        ');
        
        DB::connection('mysql-dw')->statement('
            CREATE INDEX idx_invalid ON wrk_stg_player_reg_events_valid (invalid)
        ');
        
        return true;
    }
    
    protected function addRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement('
            INSERT INTO cube_player_registrations (
                registration_id,
                dim_date_id,
                dim_player_id,
                dim_level_id,
                dim_level_type_id,
                dim_position_id,
                organization_name,
                organization_state,
                league_name,
                season_year,
                season,
                school_name,
                school_district,
                school_state,
                created_at
            ) SELECT
                w.registration_id,
                w.dim_date_id,
                w.dim_player_id,
                w.dim_leveL_id,
                w.dim_level_type_id,
                w.dim_position_id,
                w.organization_name,
                w.organization_state,
                w.league_name,
                w.season_year,
                w.season,                
                w.school_name,
                w.school_district,
                w.school_state,
                NOW()
            FROM
                wrk_stg_player_reg_events_valid AS w
            WHERE
                w.invalid = 0
            ON DUPLICATE KEY UPDATE
                dim_level_id = w.dim_leveL_id,
                dim_leveL_type_id = w.dim_leveL_type_id,
                dim_position_id = w.dim_position_id,
                organization_name = w.organization_name,
                organization_state = w.organization_state,
                league_name = w.league_name,
                season_year = w.season_year,
                season = w.season,                
                school_name = w.school_name,
                school_district = w.school_district,
                school_state = w.school_state,
                updated_at = NOW()
        ');
        return true;
    }
    
    protected function updateRecords()
    {
        return true;
    }
    
    protected function removeStagedRecords()
    {
        if ($this->maxStageId <= 0) {
            return false;
        }
        DB::connection('mysql-dw')->statement(
            'DELETE FROM stg_player_reg_events WHERE id <= ?',
            [ $this->maxStageId ]
        );
        return true;
    }
}
