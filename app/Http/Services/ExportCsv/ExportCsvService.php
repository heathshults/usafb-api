<?php
namespace App\Http\Services\ExportCsv;

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
    const EXPORT_QUERY = 'select * from registrant r inner join %s t on t.registrant_id = r.id where r.type = ?';

    /**
     * Will export the DB info to a CSV file
     * @param string $type The type of the CSV (PLAYER, COACH)
     * @return File The csv file
    */
    public function exportData($type)
    {
        switch ($type) {
            case self::TYPE_PLAYER:
            default:
                $query = sprintf(self::EXPORT_QUERY, 'player');
                break;
            case self::TYPE_COACH:
                $query = sprintf(self::EXPORT_QUERY, 'coach');
                break;
        }

        $result = DB::select($query, [$type]);
        $array = json_decode(json_encode($result), true);

        return $array;
    }
}
