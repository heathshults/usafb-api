<?php

namespace Tests\Unit;

use Mockery;
use org\bovigo\vfs\vfsStream;
use App\Http\Services\ExportCsv\ExportCsvService;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ExportCsvServiceTest extends \TestCase
{
    use DatabaseMigrations;


    /**
    * Should test that service returns array for player export
    */
    public function testShouldReturnArrayForPlayerExport() {
        $exportService = new ExportCsvService;
        $response = $exportService->exportData('PLAYER');
        $this->assertTrue(is_array($response));
    }
    
    /**
    * Should test that service returns array for coach export
    */
    public function testShouldReturnArrayForCoachExport() {
        $exportService = new ExportCsvService;
        $response = $exportService->exportData('COACH');
        $this->assertTrue(is_array($response));
    }

}