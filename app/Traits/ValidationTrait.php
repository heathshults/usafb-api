<?php
namespace App\Traits;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait ValidationTrait
{
    protected static $DATE_FORMAT = 'Y-m-d';

    abstract protected static function getValidationMapping();

    /**
    *   Will return the value if a value is present.
    *   If value is not present it will through an exception
    *   @param $value value to test if null or empty
    *   @return passed value
    */
    public static function required($item, $value)
    {
        if ((trim($value) == '' || is_null($value))) {
            throw new BadRequestHttpException('Required Value not present for item '. $item);
        } else {
            return $value;
        }
    }

    /**
    *   Will through an Exception if $value is present
    *   @param $value a value
    *   @return null
    */
    public static function requiredEmpty($item, $value)
    {
        if ((trim($value) == '' || is_null($value))) {
            return null;
        } else {
            throw new BadRequestHttpException('Value is present, and expected empty: '. $item . ' - '. $value);
        }
    }

    /**
    *   Will through an Exception if $value is present
    *   @param $value a value
    *   @return null
    */
    public static function notRequired($item, $value)
    {
        return $value;
    }

    /**
    *   Will return pased value to date
    *   @param $value? as read from csv file
    *   @return Date parsed date
    */
    public static function parseToDate($item, $value)
    {
        $parsedDate = strtotime($value);
        if ($parsedDate) {
            return date(self::$DATE_FORMAT, strtotime($value));
        } else {
            throw new BadRequestHttpException('Cant parse that date '. $item . ' - ' . $value . '. Date format: m/d/Y. e.g.: 12/01/2017');
        }
    }

    /**
    *   Will return pased value to boolean
    *   @param $value? as read from csv file
    *   @return Boolean parsed string
    */
    public static function parseToBoolean($item, $value)
    {
        if (strtoupper($value) === 'YES' || $value === '1' || $value === 1  || $value === true) {
            return true;
        } elseif (strtoupper($value) === 'NO' || $value === '0'  || $value === 0 || $value === '' || $value === false) {
            return false;
        } else {
            throw new BadRequestHttpException('Cant parse that string to boolean '. $item . ' - ' . $value . '. Boolean formats allowed: YES, 1, true - NO, 0, false');
        }
    }

    public static function validate($data)
    {
        foreach (self::getValidationMapping() as $key => $value) {
            if (isset($data[$key])) {
                $data[$key] = self::$value($key, $data[$key]);
            }
        }

        return $data;
    }
}
