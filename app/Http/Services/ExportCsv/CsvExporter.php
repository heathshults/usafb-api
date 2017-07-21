<?php
namespace App\Http\Services\ExportCsv;

class CsvExporter
{
    public function arrayToCsv(array $fieldsArray)
    {
        if (count($fieldsArray) > 0) {
            $firstElement = $fieldsArray[0];
            $rowKeys = array_keys($fieldsArray[0]);
            $header = implode(",", $rowKeys);
            $firstLine = $header."\n";
            return array_reduce($fieldsArray, function ($accum, $row) {
                $values = array_values($row);
                $stringLine = $accum.implode(",", $values)."\n";
                return $stringLine;
            }, $firstLine);
        }
    }
}
