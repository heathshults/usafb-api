<?php

namespace App\Http\Services;

use App\Models\Import;
use App\Helpers\Services\ImportPlayerService;
use App\Helpers\Services\ImportCoachService;

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
        if ($recordType == Import::TYPE_COACHES) {
            return new ImportPlayerService();
        } elseif ($recordType == Import::TYPE_PLAYERS) {
            return new ImportCoachService();
        } else {
            throw new Exception('Invalid type. Unable to build factory.');
        }
    }
}
