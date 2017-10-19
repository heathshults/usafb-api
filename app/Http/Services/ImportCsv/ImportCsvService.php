<?php
namespace App\Http\Services\ImportCsv;

use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Models\Registrant;
use App\Models\Registration;
use App\Models\Source;
use App\Models\Player;
use App\Models\PlayerRegistration;
use App\Models\Coach;
use App\Models\CoachRegistration;
use App\Models\ParentGuardian;
use Illuminate\Support\Facades\DB;
use App\Helpers\FunctionalHelper;
use App\Helpers\DateHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/*
    ImportCSV Service
    Service for importing csv to player
*/
class ImportCsvService
{
    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';
    const CSV_NUMBER_FIELDS_PLAYER = 53;
    const CSV_NUMBER_FIELDS_COACH = 30;
    const LEGAL_AGE = 18;
    const CSV_MAX_LINE_LENGTH = 1000;
    private $fileLine = null;
    private $indexMapperArray = null;
    private $modelService = null;

    /**
     * Initialize auth service
     *
     * @constructor
     */
    public function __construct()
    {
        $this->modelService = app('Model');
    }

    /**
     * Will process the file and return an array with the result
     * Will show increment erros if line is not as big as expected (not the required amount of commas),
     * On parsing errors,
     * On Required fields missing
     * @param File: file to process
     * @param string $type The type of the CSV (PLAYER, COACH)
     * @param string $apiKey The source api key
     * @return array of processed rules and error
    */
    public function importCsvFile($file, $type, $apiKey = 'USFBKey')
    {
        $fd = fopen($file, 'r');

        $header = fgetcsv($fd);

        $lineAmount = $this->getLineAmountByType($type);

        if (!ImportCsvUtils::isLineAsExpected($header, $lineAmount)) {
            return [
                'processed' => 0,
                'errors' => 1
            ];
        }

        $this->indexMapperArray = ImportCsvUtils::columnToIndexMapper($header);
        $linesProcessed = 0;
        $errors = 0;
        DB::connection()->disableQueryLog();
        $sourceId = DB::table('source')->select('id')->where('api_key', $apiKey)->first()->id;
        while (($this->fileLine = fgetcsv($fd, self::CSV_MAX_LINE_LENGTH, ",")) !== false) {
            $this->fileLine = ImportCsvUtils::mapCsvColumnsToTableFields(
                array_combine($this->indexMapperArray, $this->fileLine)
            );
            DB::beginTransaction();
            if (false) {
                $errors ++;
            } else {
                try {
                    $this->modelService->create($type, $sourceId, $this->fileLine);

                    $linesProcessed++;
                    DB::commit();
                } catch (BadRequestHttpException $e) {
                    $errors++;
                    DB::rollBack();
                }
            }
        }

        return ['processed' => $linesProcessed,
                'errors' => $errors];
    }

    /**
    * Returns the expected csv line Amount based on the import type
    * @param string $type The type of the CSV (PLAYER, COACH)
    * @return integer line Amount
    */
    public function getLineAmountByType($type)
    {
        $lineAmount = 0;

        switch ($type) {
            case self::TYPE_PLAYER:
                $lineAmount = self::CSV_NUMBER_FIELDS_PLAYER;
                break;
            case self::TYPE_COACH:
                $lineAmount = self::CSV_NUMBER_FIELDS_COACH;
                break;
        }

        return $lineAmount;
    }
}
