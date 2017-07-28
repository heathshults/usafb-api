<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\ImportCsv\ImportCsvService;

class UploadController extends Controller
{
    const TYPE = 'PLAYER';
    /**
     * Will retun a json object with processed and errors keys
     * @param Illuminate\Http\Request $request with a csv_file
     * @return JsonObject
     * {
     *    processes: n,
     *    errros: i
     * }
     * holding the amount of processed items `n` and the amount of errors `i`
     */
    public function processFileUpload(Request $request)
    {
        $importService = new ImportCsvService();
        return response()->
                json($importService->importCsvFile($request->file('csv_file'), self::TYPE));
    }
}
