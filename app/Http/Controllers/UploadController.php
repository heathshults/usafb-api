<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\ImportCsv\ImportCsvService;
use App\Http\Services\ExportCsv\CsvExporter;
use App\Models\Player;

class UploadController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function processFileUpload(Request $request)
    {
        $importService = new ImportCsvService();
        
        return response()->
                json($importService->importCsvFile($request->file('csv_file')));
    }

    public function exportPlayers(Request $request)
    {
        $exporter = new CsvExporter;
        $players = Player::all();
        $playersArray = $players->toArray();
 
        $headers = array(
            'Content-Disposition'=> 'attachment; filename="export.csv"',
            'Cache-control' => 'private',
            'Content-type' => 'application/force-download',
            'Content-transfer-encoding'=> 'text'
        );

        return response($exporter->arrayToCsv($playersArray))->withHeaders($headers);
    }
}
