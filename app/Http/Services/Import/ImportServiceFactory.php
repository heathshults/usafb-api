<?php

namespace App\Http\Services\Import;

use App\Models\Import as Import;
use App\Http\Services\Import\ImportPlayerService as ImportPlayerService;
use App\Http\Services\Import\ImportCoachService as ImportCoachService;
use Illuminate\Support\Facades\Log;

/**
 * ImportServiceFactory
 * Factory for import records
 *
 * @package    Http
 * @subpackage Services
 */

class ImportServiceFactory
{
    public static function build($recordType)
    {
        Log::debug('Getting ImportService for type ('.$recordType.')');
        if ($recordType == Import::TYPE_COACHES) {
            Log::debug('Instantiating new coach import service.');
            return new ImportCoachService();
        } elseif ($recordType == Import::TYPE_PLAYERS) {
            Log::debug('Instantiating new player import service.');
            return new ImportPlayerService();
        } else {
            throw new Exception('Invalid type. Unable to build factory.');
        }
    }
}
