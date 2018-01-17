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
     * Upload a file to the s3 bucket
     * @param  string $filePath  The file path
     * @param  string $objectKey The s3 file name
     * @return AWS\Result        The AWS Result
     */
    public function s3PutObject($filePath, $objectKey)
    {
        $result = $this->s3Client->putObject(
            [
                'Bucket'     => getenv('AWS_BUCKET_DEFAULT'),
                'Key'        => $objectKey,
                'SourceFile' => $filePath
            ]
        );
        return $result;
    }

    /**
     * Download a file from the application's s3 bucket
     * @param  string $objectKey The s3 file name
     * @return AWS\Result        The AWS Result
     */
    public function s3GetObject($objectKey)
    {
        $result = $this->s3Client->getObject(array(
            'Bucket' => getenv('AWS_BUCKET_DEFAULT'),
            'Key'    => $objectKey
        ));
        return $result;
    }
    
    /**
     * Remove a file from the application's s3 bucket
     * @param  string $objectKey The s3 file name
     * @return AWS\Result        The AWS Result
     */
    public function s3DeleteObject($objectKey)
    {
        $result = $this->s3Client->deleteObject(array(
            'Bucket' => getenv('AWS_BUCKET_DEFAULT'),
            'Key'    => $objectKey
        ));
        return $result;
    }
}
