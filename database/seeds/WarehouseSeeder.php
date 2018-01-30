<?php

use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $connection = DB::connection('mysql-dw');
        $pdo = $connection->getpdo();
        
        // disable foreign key checking
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        // purge/truncate all dimensions
        $pdo->exec('TRUNCATE TABLE dim_dates');
        $pdo->exec('TRUNCATE TABLE dim_locations');
        $pdo->exec('TRUNCATE TABLE dim_levels');
        $pdo->exec('TRUNCATE TABLE dim_level_types');
        $pdo->exec('TRUNCATE TABLE dim_genders');
        $pdo->exec('TRUNCATE TABLE dim_ages');
        $pdo->exec('TRUNCATE TABLE dim_positions');
        
        // dim_dates
        $pdo->exec('LOAD DATA LOCAL INFILE "'.dirname(__FILE__).'/data/warehouse_dim_dates.txt'.'" INTO TABLE `dim_dates` FIELDS TERMINATED BY "|" IGNORE 1 LINES ('.
            '`id`, `calendar_date`, `calendar_day`, `calendar_month`, `calendar_year`, `calendar_mon_d_y`, `calendar_m_d_y`,'.
            '`reporting_y_q_w`, `reporting_y_w`, `reporting_y_w_d`, `day_of_week_number`, `day_of_week_name`, `day_of_week_short_name`,'.
            '`day_of_week_abv_name`, `week_number`, `week_of_month_number`, `week_short_name`, `month_number`, `month_name`,'.
            '`month_short_name`, `day_of_month_number`, `quarter_number`, `quarter_name`, `day_of_quarter_number`, `year_number`,'.
            '`year_name`,`day_of_year_number`)');
        
        $dataDir = dirname(__FILE__).'/data';
        
        // dim_genders
        $pdo->exec("LOAD DATA LOCAL INFILE '${dataDir}/warehouse_dim_genders.txt' INTO TABLE dim_genders FIELDS TERMINATED BY '|' IGNORE 1 LINES (`gender`,`gender_name`)");
        
        // dim_levels
        $pdo->exec("LOAD DATA LOCAL INFILE '${dataDir}/warehouse_dim_levels.txt' INTO TABLE dim_levels FIELDS TERMINATED BY '|' IGNORE 1 LINES (`level`,`level_name`)");
                
        // dim_level_types
        $pdo->exec("LOAD DATA LOCAL INFILE '${dataDir}/warehouse_dim_level_types.txt' INTO TABLE dim_level_types FIELDS TERMINATED BY '|' IGNORE 1 LINES (`level_type`,`level_type_name`)");

        // dim_positions
        $pdo->exec("LOAD DATA LOCAL INFILE '${dataDir}/warehouse_dim_positions.txt' INTO TABLE dim_positions FIELDS TERMINATED BY '|' IGNORE 1 LINES (`position_type`,`position`,`position_name`,`position_abbreviation`)");
        
        // dim_ages
        $pdo->exec("LOAD DATA LOCAL INFILE '${dataDir}/warehouse_dim_ages.txt' INTO TABLE dim_ages FIELDS TERMINATED BY '|' IGNORE 1 LINES (`age`,`age_group`)");
                    
        // dim_locations
        $pdo->exec("LOAD DATA LOCAL INFILE '${dataDir}/warehouse_dim_locations.txt' INTO TABLE dim_locations FIELDS TERMINATED BY '|' IGNORE 1 LINES (`zip`,`city`, `county`, `county_fips`, `state`, `area_code`, `latitude`, `longitude`)");
                
        // turn fk checking back on
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');        
    }
}