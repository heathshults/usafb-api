<?php

use App\Http\Services\ExportCsv\CsvExporter;
use App\Models\Player;
/*
This file was only created for demo purposes. not to be used in prod
*/
class InserterBuilderTest extends \TestCase
{
    public function testShouldReturnArrayAsStringSeparatedByLines()
    {
         $players = App\Models\Player::all();
         $amountOfRows = count($players->toArray());
         $exporter = new CsvExporter;
         $csv = $exporter -> arrayToCsv($players->toArray());
         $this->assertTrue(substr_count($csv, "\n") == $amountOfRows + 1);
    }
}