<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Services\ExportCsv\ExportCsvService;
use Illuminate\Validation\Validator;

class DownloadController extends Controller
{
    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';

    /**
     * Will retun a csv file with the player or coaches information
     * @param Illuminate\Http\Request $request with a csv_file
     * @return File the CSV file
     */
    public function downloadFile(Request $request)
    {
        $request->replace(['type' => strtoupper($request->input('type'))]);

        $this->validate(
            $request,
            [
                'type' => 'required|in:' . self::TYPE_PLAYER . ','. self::TYPE_COACH
            ],
            [
                'type.in' => "The selected type is invalid. " .
                "Allowed types: " . self::TYPE_PLAYER . ", " . self::TYPE_COACH,
            ]
        );

        $type = $request->input('type');

        $headers = array(
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=" . strtolower($type) . ".csv"
        );

        $exportService = new ExportCsvService();
        $data = $exportService->exportData($type);
        $columns = null;

        if (!empty($data)) {
            $columns = array_keys($data[0]);
        }

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            if ($columns !== null) {
                fputcsv($file, $columns);
            }
            foreach ($data as $line) {
                fputcsv($file, $line);
            }
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
