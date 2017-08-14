<?php
namespace App\Http\Services\ExportCsv;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Http\Services\ImportCsv\ImportCsvUtils;
use Illuminate\Support\Facades\DB;

/**
 *  ExportCSV Service
 *  Service for exporting csv from DB
 */
class ExportCsvService
{
    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';
    const EXPORT_QUERY_COACH_COLUMNS = "SELECT " .
                                        "array_to_string(ARRAY(SELECT 'r' || '.' || c.column_name
                                            FROM information_schema.columns As c
                                                WHERE table_name = 'registrant' 
                                                AND  c.column_name NOT IN('id')
                                        ), ',') || ',' || " .
                                        "array_to_string(ARRAY(SELECT 'c' || '.' || c.column_name FROM ".
                                        "information_schema.columns As c WHERE table_name = 'coach' AND " .
                                        " c.column_name NOT IN('id', 'registrant_id')), ',') as columns";
    const EXPORT_QUERY_COACH = 'SELECT %s from registrant r inner join coach c on c.registrant_id = r.id';

    const EXPORT_QUERY_PLAYER_COLUMNS = "SELECT " .
                                        "array_to_string(ARRAY(SELECT 'r' || '.' || c.column_name
                                            FROM information_schema.columns As c
                                                WHERE table_name = 'registrant' 
                                                AND  c.column_name NOT IN('id')
                                        ), ',') || ',' || " .
                                        "array_to_string(ARRAY(SELECT 'p' || '.' || c.column_name FROM " .
                                        "information_schema.columns As c WHERE table_name = 'player' " .
                                        "AND  c.column_name NOT IN('id', 'registrant_id')), ',') || ',' || " .
                                        "array_to_string(ARRAY(SELECT 'pg' || '.' || c.column_name FROM ".
                                        "information_schema.columns As c WHERE table_name = ".
                                        "'parent_guardian' AND  c.column_name NOT IN('id', ".
                                        "'player_registration_id')), ',') as columns";
    const EXPORT_QUERY_PLAYER = 'SELECT %s from registrant r ' .
                                'inner join player p on p.registrant_id = r.id '.
                                'inner join (SELECT DISTINCT on (registrant_id) registrant_id, id FROM '.
                                'registration order BY registrant_id, created_at DESC) reg on '.
                                'reg.registrant_id = r.id '.
                                'inner join player_registration pr on pr.registration_id = reg.id '.
                                'inner join parent_guardian pg on pg.player_registration_id = pr.id';

    /**
     * Will export the DB info to a CSV file
     * @param string $type The type of the CSV (PLAYER, COACH)
     * @return File The csv file
    */
    public function exportData($type)
    {
        switch ($type) {
            case self::TYPE_PLAYER:
                $queryColumns = self::EXPORT_QUERY_PLAYER_COLUMNS;
                $queryBody = self::EXPORT_QUERY_PLAYER;
                break;
            case self::TYPE_COACH:
                $queryColumns = self::EXPORT_QUERY_COACH_COLUMNS;
                $queryBody = self::EXPORT_QUERY_COACH;
                break;
            default:
                throw new BadRequestHttpException('No data for type: ' . $type);
                break;
        }

        $columns = DB::select($queryColumns);
        $result = DB::select(sprintf($queryBody, $columns[0]->columns));
        $array = json_decode(json_encode($result), true);

        return $array;
    }
}
