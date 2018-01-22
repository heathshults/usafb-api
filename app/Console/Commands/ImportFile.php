<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\Coach;
use App\Exceptions\ImportServiceException;
use App\Http\Services\Import\ImportServiceFactory;
use App\Http\Services\Import\ImportCoachService;
use App\Http\Services\Import\ImportPlayerService;
use App\Http\Services\Import\ImportServiceResult;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;

class ImportFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:file {type} {file}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import player or coach file (specified by type).';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');
        if (is_null($type) || (!is_null($type) && ($type != 'players' && $type != 'coaches'))) {
            $this->error('Invalid or missing type, expecting "players" or "coaches".');
            return false;
        }
        $file = $this->argument('file');        
        if (is_null($file) || (!is_null($file) && !file_exists($file))) {
            $this->error('Invalid or missing file.');
            return false;
        }
        try {
            Log::info('Starting '.$type.' import.');
            Log::debug('Finding ImportService for type ('.$type.')');
            $importServiceFactory = new ImportServiceFactory();
            $importService = $importServiceFactory->build($type);
            Log::info('Built ('.get_class($importService).') from ImportServiceFactory.');
            Log::info('Importing from file ('.$file.') ...');
            $importResult = $importService->importFile($file);
            Log::info('Import completed.');
            Log::info('Number of Records: '.$importResult->numRecords);
            Log::info('Number Imported: '.$importResult->numImported);
            Log::info('Number Errors: '.$importResult->numErrors);
            if ($importResult->numErrors > 0) {
                foreach ($importResult->errors() as $row => $errors) {
                    Log::error('Error on Row #'.$row.' - '.implode(', ', $errors));
                }
            }
            Log::info('Finished.');
        } catch (ImportServiceException $importException) {
            $this->error($importException->getMessage());
        } catch (Exception $exception) {
            $this->error($importException->getMessage());            
        }
    }
}
