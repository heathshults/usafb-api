<?php

namespace App\Http\Services;

use Aws\Laravel\AwsFacade;
use Aws\S3\Exception\S3Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Helpers\FileHelper;

/**
 * Manage AWS services
 *
 * @package    Http
 * @subpackage Services
 * @author     Matias Blanco <matias.blanco@bluestarsports.com>
 */
class AwsService
{
    protected $s3Client;

    /**
     * Initialize auth service
     *
     * @constructor
     */
    public function __construct()
    {
        $this->s3Client = AwsFacade::createClient('s3');
    }

    /**
     * This generates the report json and unique files names to upload it to s3 bucket
     * @param  File $file        The uploading file
     * @param  string $type      The file type
     * @return array             Contains the json report and csv file paths at S3
     */
    public function uploadFile($file, $type, $loggedUserEmail, $apiKey = 'USFBKey')
    {
        $filePath = $file->getRealPath();
        $objectKey = uniqid(substr($type, 0, 1));
        $jsonReport = $this->jsonReport($file, $objectKey, $type, $loggedUserEmail, $apiKey);

        $jsonReportFilePath = FileHelper::createTempFile('report', json_encode($jsonReport));
        $s3JsonFileResult = $this->s3PutObject($jsonReportFilePath, $jsonReport->meta['report']);
        unlink($jsonReportFilePath);
        $s3CsvFileResult = $this->s3PutObject($filePath, $jsonReport->meta['csv']);

        return ['report' => $s3JsonFileResult->get('ObjectURL'), 'csv' => $s3CsvFileResult->get('ObjectURL')];
    }

    /**
     * Generates the JSON Report to be uploaded
     * @param  File $file                The file to extract the header
     * @param  string $objectKey         The unique file name for the s3 bucket
     * @param  string $type              The CSV type (Player, Coach)
     * @param  sstring $loggedUserEmail  The logged user email
     * @param  string $apiKey            The api key
     * @return stdClass                  The json as PHP stdClass
     */
    private function jsonReport($file, $objectKey, $type, $loggedUserEmail, $apiKey)
    {
        $fd = fopen($file, 'r');
        $header = fgetcsv($fd);
        fclose($fd);

        $obj = new \stdClass();
        $obj->meta = [
          'csv' => $objectKey.'.csv',
          'report' => $objectKey.'.json',
          'type' => $type,
          'apiKey' => $apiKey,
          'email' => $loggedUserEmail,
          'totalRows' => '',
          'successRows' => '',
          'failedRows' => ''
        ];
        $obj->usafbids_created = [];
        $obj->header = base64_encode(implode(',', $header));
        $obj->urls = [
          'originalCsv' => '',
          'reportHtml' => '',
          'errorsCsv' => ''
        ];
        $obj->general_errors = [];
        $obj->specific_errors = [];

        return $obj;
    }

    /**
     * This upload a file to the s3 bucket
     * @param  string $filePath  The file path
     * @param  string $objectKey The s3 file name
     * @return AWS\Result        The AWS Result
     */
    public function s3PutObject($filePath, $objectKey)
    {
        try {
            $result = $this->s3Client->putObject(
                [
                    'Bucket'     => getenv('AWS_UPLOAD_BUCKET'),
                    'Key'        => $objectKey,
                    'SourceFile' => $filePath
                ]
            );

            return $result;
        } catch (S3Exception $e) {
            throw new BadRequestHttpException($e->getAwsErrorMessage());
        }
    }
}
