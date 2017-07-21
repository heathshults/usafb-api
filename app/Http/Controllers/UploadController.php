<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\ImportCsv\ImportCsvService;

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
}
