<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\ImportCsv\ImportCsvService;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use App\Http\Services\ImportCsv\ImportCsvUtils;

class UploadController extends Controller
{
    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';
    const CSV_MAX_ROWS = 2500;

    /**
     * Will retun a json object with processed and errors keys
     * @param Illuminate\Http\Request $request with a csv_file
     * @return JsonObject holding the amount of processed items `n` and the amount of errors `i`
     */
    public function processFileUpload(Request $request)
    {
        ValidatorFacade::extend('maxrows', function ($attribute, $value, $parameters) {
            return ImportCsvUtils::countRows($value) < self::CSV_MAX_ROWS;
        });

        $request->replace(['type' => strtoupper($request->input('type'))]);

        $this->validate(
            $request,
            [
                'csv_file' => 'required|mimes:csv,txt|maxrows',
                'type' => 'required|in:' . self::TYPE_PLAYER . ','. self::TYPE_COACH
            ],
            [
                'type.in' => "The selected type is invalid. " .
                "Allowed types: " . self::TYPE_PLAYER . ", " . self::TYPE_COACH,
                'maxrows' => "The file exceeds the max rows allowed. " . self::CSV_MAX_ROWS
            ]
        );
        $importService = new ImportCsvService();
        return response()->
                json(
                    $importService->importCsvFile($request->file('csv_file'), $request->input('type'))
                );
    }
}
