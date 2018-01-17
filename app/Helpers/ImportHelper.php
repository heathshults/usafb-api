<?php

namespace App\Helpers;

use Datetime;
use DateInterval;

class ImportHelper
{
    /**
     * Validate the max rows of the csv
     * @param File $file The csv file
     * @return int the number of rows
     */
    public static function countRows($file)
    {
        $fp = file($file);
        return count($fp);
    }
}
