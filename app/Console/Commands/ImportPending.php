<?php

namespace App\Console\Commands;

use App\Models\Import;
use App\Models\Player;
use App\Models\Coach;
use App\Exceptions\ImportServiceException;
use App\Http\Services\AwsService;
use App\Http\Services\Import\ImportServiceFactory;
use App\Http\Services\Import\ImportCoachService;
use App\Http\Services\Import\ImportPlayerService;
use App\Http\Services\Import\ImportServiceResult;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class ImportPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:pending';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import/process pending Player or Coach files.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('Finding Pending Import files.');
        $imports = Import::pending()->get();
        $numPending = $imports->count();
        if ($numPending <= 0) {
            Log::info('No pending imports found. Exiting.');
            return;
        }
        Log::debug('Found ('.$numPending.') pending imports.');
        
        $awsService = null;
        try {
            $awsService = new AwsService();
            if (is_null($awsService)) {
                throw new Exception("Unable to obtain instance of AwsService.");
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
        }
                        
        $importServiceFactory = new ImportServiceFactory();
        foreach ($imports as $import) {
            $importType = $import->type;
            $importService = $importServiceFactory->build($importType);
            try {
                $originalFileName = $import->file_name;
                $remoteFilePath = $import->file_path_remote;
                $remoteFileName = basename($remoteFilePath);
                
                if (is_null($remoteFilePath)) {
                    Log::debug('No remote file path found for Import ('.$import->id.')');
                    continue;
                }
                
                if (!$importService->fileExtensionSupported($originalFileName)) {
                    Log::info('Skipping file ('.$remoteFileName.'). File extension not supported.');
                    continue;
                }
                
                Log::debug('Downloading remote file ('.$remoteFileName.') from S3.');
                $s3Result = $awsService->s3GetObject($remoteFileName);
                $s3ContentType = $s3Result->get('ContentType');
                $s3Content = $s3Result->get('Body');
                
                if (empty($s3Content)) {
                    Log::error('File content missing or invalid. Skipping.');
                    continue;
                }
                    
                Log::info('Importing content from remote file ('.$remoteFileName.').');
                $importServiceResult = $importService->importContent($s3Content);
                Log::info('Finished importing record from ('.$remoteFileName.').');
                Log::info('Number of Records: '.$importServiceResult->numRecords);
                Log::info('Number Imported: '.$importServiceResult->numImported());
                Log::info('Number Errors: '.$importServiceResult->numErrors());
                
                $import->status = Import::STATUS_COMPLETED;
                $import->num_records = $importServiceResult->numRecords;
                $import->num_imported = $importServiceResult->numImported();
                $import->num_errors = $importServiceResult->numErrors();
                $import->errors = $importServiceResult->errors();
                $import->results = $importServiceResult->results();
                $import->save();
            } catch (ImportServiceException $importServiceException) {
                Log::error('ImportServiceException occurred while processing file.');
                Log::error($importServiceException->getMessage());
                $import->status = Import::STATUS_FAILED;
                $import->status_details = $importServiceException->getMessage();
                $import->save();
            } catch (Exception $exception) {
                Log::error('Exception occurred while processing files.');
                Log::error($exception->getMessage());
                $import->status = Import::STATUS_FAILED;
                $import->status_details = 'Unknown error occurred';
                $import->save();
            }
        }
        
        Log::info('Finished.');
    }
}
