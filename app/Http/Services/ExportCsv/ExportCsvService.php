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
    public function exportCoachData()
    {
    }

    public function exportPlayerData()
    {
    }

    /**
     * Will export the DB info to a CSV file
     * @param string $type The type of the CSV (PLAYER, COACH)
     * @return File The csv file
    */
    public function exportData()
    {
    }
}
