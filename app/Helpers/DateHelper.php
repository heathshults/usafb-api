<?php

namespace App\Helpers;

use Datetime;
use DateInterval;

class DateHelper
{

    /** Used to add n seconds to a given data/time */
    const SECONDS_INTERVAL_PRINTF = 'PT%dS';

    /** Used to define the standard date/time foremat in Zulu or UTC-0 format */
    const UTC_DATE_TIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
    * Checks to see if a start date & timer with number of seconds to expires,
    * checks to see if it is still active.
    * active.
    * @param String representing a start date and time Example: '2017-07-11T19:04:10Z'
    * @param int seconds to expire Example: 10000
    * @param String time reference (default=now)
    * @return True if date has not expired or is still acive
    */
    public static function isDateActive($startDateTime, int $secondsToExpire, $timeReference = 'now')
    {
        $startTime = new DateTime($startDateTime);
        $endTime = $startTime->add(new DateInterval(sprintf(self::SECONDS_INTERVAL_PRINTF, $secondsToExpire)));
        $isExpired = false;

        if (strtotime($endTime->format(self::UTC_DATE_TIME_FORMAT)) > strtotime($timeReference)) {
            $isExpired = true;
        }

        return $isExpired;
    }

    /**
    * Gets the years between the param date and today.
    * @param String $date Represents a date string.
    * @return Integer The number of years between $date param and today
    */
    public static function getYearsFromDateToNow($date)
    {
        $date = new DateTime($date);
        $now = new DateTime();
        return $date->diff($now)->y;
    }
}
