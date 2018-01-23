<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Import;
use App\Http\Services\AwsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webpatser\Uuid\Uuid;

/**
 * ImportsController
 * Manage imports for players and coaches (files stored on S3)
 *
 * @package    Http
 * @subpackage Controllers
 */
class ImportsController extends Controller
{

    protected $awsService;
        
    public function __construct(AwsService $awsService)
    {
        $this->awsService = $awsService;
    }
    
    /**
     * Get player/coach imports (records)
     * Url: GET /imports/(players|coaches)
     *
     * @param Request $request
     *
     * @return json
     */
    public function index(Request $request, $type)
    {
        $sort = $this->buildSortCriteria($request->query(), [ 'column' => 'created_at', 'order' => 'desc' ]);
        $paginationCriteria = $this->buildPaginationCriteria($request->query());
        $query = call_user_func('App\Models\Import::'.$type);
        $imports = $query->orderBy($sort['column'], $sort['order'])->paginate($paginationCriteria['per_page']);
        return $this->respond('OK', $imports);
    }

    /**
     * Get a specific player or coach import record
     * Url: GET /imports/(players|coaches)/:id
     *
     * @param Request $request
     * @param string $id
     *
     * @return json
     */
    public function show(Request $request, $type, $id)
    {
        $import = call_user_func('App\Models\Import::'.$type)->find($id);
        if (is_null($import)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Import ('.$id.') not found.']]);
        }
        return $this->respond('OK', $import);
    }

    /**
     * Get a specific Player or Coach import source
     * Url: GET /imports/(players|coaches)/:id/source
     *
     * @param Request $request
     * @param string $recordType (players|coaches)
     * @param string $recordId
     *
     * @return json
     */
    public function source(Request $request, $recordType, $recordId)
    {
        $import = call_user_func('App\Models\Import::'.$recordType)->find($recordId);
        if (is_null($import)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Import ('.$id.') not found.']]);
        }
        $filePath = $import->file_path_remote;
        if (is_null($filePath)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'No source file found for Import ('.$id.').'
                ]
            ]);
        }
        $fileName = basename($filePath);
        try {
            $s3Result = $this->awsService->s3GetObject($fileName);
            $data = [
                'file_name' => $fileName,
                'content_type' => $s3Result['ContentType'],
                'content_size' => $s3Result['ContentLength'],
                'content' => base64_encode($s3Result['Body']),
            ];
            return $this->respond('OK', $data);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            throw new BadRequestHttpException('An error occurred while accessing source file.');
        }
    }
        
    public function results(Request $request, $recordType, $recordId)
    {
        Log::debug('Record Type: '.$recordType);
        $import = call_user_func('App\Models\Import::'.$recordType)->find($recordId);
        if (is_null($import)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Import ('.$recordId.') not found.']]);
        }
        if ($import->status != Import::STATUS_COMPLETED) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'No results available. File not processed.']]);
        }
        $csvResults = $import->resultsToCSV();
        $data = [
            'file_name' => $import->id.'-results.csv',
            'content_type' => 'text/plain',
            'content_size' => (empty($csvResults) ? 0 : strlen($csvResults)),
            'content' => base64_encode($csvResults),
        ];
        return $this->respond('OK', $data);
    }

    public function errors(Request $request, $recordType, $recordId)
    {
        Log::debug('Record Type: '.$recordType);
        $import = call_user_func('App\Models\Import::'.$recordType)->find($recordId);
        if (is_null($import)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Import ('.$id.') not found.']]);
        }
        if ($import->status != Import::STATUS_COMPLETED) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'No errors available. File not processed.']]);
        }
        $csvResults = $import->errorsToCSV();
        $data = [
            'file_name' => $import->id.'-errors.csv',
            'content_type' => 'text/plain',
            'content_size' => (empty($csvResults) ? 0 : strlen($csvResults)),
            'content' => base64_encode($csvResults),
        ];
        return $this->respond('OK', $data);
    }
    
    /**
     * Store (upload) player import into S3 and queue it for processing
     * Url: POST /imports/coaches
     *
     * @param Request $request
     *
     * @return json
     */
    public function upload(Request $request, $type)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if (!$file->isValid()) {
                throw new BadRequestHttpException('Invalid or missing file upload.');
            }
            $fileName = $file->getClientOriginalName();
            $fileSourcePath = $file->getRealPath();
            $fileRemoteName = ($fileName.'.'.microtime(true));
            // TODO quick validation before uploading
            try {
                // upload to s3 bucket
                $s3Result = $this->awsService->s3PutObject($fileSourcePath, $fileRemoteName);
                $fileRemotePath = $s3Result->get('ObjectURL');
                
                // get user performing the upload
                $user = $request->user();
                
                // create new (pending) import record
                $import = new Import();
                $import->user_id = $user->id;
                $import->type = $type;
                $import->file_name = $fileName;
                $import->file_path_remote = $fileRemotePath;
                                                                
                if ($import->save()) {
                    return $this->respond('OK', $import);
                } else {
                    $this->awsService->s3DeleteObject($fileRemoteName);
                    throw new BadRequestHttpException('Error creating import record.');
                }
            } catch (S3Exception $s3ex) {
                throw new BadRequestHttpException('Unknown error occurred during upload.');
                Log::error($s3ex->getMessage());
            } catch (Exception $ex) {
                throw new BadRequestHttpException('Unknown error occurred during upload.');
                Log::error($ex->getMessage());
            }
        } else {
            throw new BadRequestHttpException('Invalid or missing file upload.');
        }
    }
}
