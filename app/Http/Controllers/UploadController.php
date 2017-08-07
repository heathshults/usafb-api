<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\ImportCsv\ImportCsvService;
use Illuminate\Validation\Validator;

class UploadController extends Controller
{
    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';
    /**
     * Will retun a json object with processed and errors keys
     * @param Illuminate\Http\Request $request with a csv_file
     * @return JsonObject holding the amount of processed items `n` and the amount of errors `i`
     */
    public function processFileUpload(Request $request)
    {

        $request->replace(['type' => strtoupper($request->input('type'))]);
        $this->validate(
            $request,
            [
                'type' => 'required|in:' . self::TYPE_PLAYER . ','. self::TYPE_COACH
            ],
            [
                'type.in' => "The selected type is invalid. 
                Allowed types: " . self::TYPE_PLAYER . ", " . self::TYPE_COACH
            ]
        );
        $importService = new ImportCsvService();
        return response()->
                json(
                    $importService->importCsvFile($request->file('csv_file'), $request->input('type'))
                );
    }
}
