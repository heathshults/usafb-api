<?php

namespace App\Helpers;

class UsafbIdHelper
{

    /**
    * The standard symbols used by PHP for converting a base_10 number
    * to base_31. Example 11 (base 10) = a (base 31).
    */
    const BASE_SYMBOLS =  '0123456789abcdefghijklmnopqrstu';

    /**
    * To prevent having readable words we will define our base 31
    * number symbols with no vowles. Using this symbol set
    * a 10 (base 10) is now B (base 31, using these symbols).
    */
    const USAFID_SYMBOLS = '0123456789BCDFGHJKLMNPQRSTVWXYZ';

    /** The base used for representing the USAFB_IDs */
    const USAFID_BASE = 31;

    /** The first digit in our series to prevent leading 0's (base 10) */
    const USAFID_MIN = 88750368;

    /* The maximum value a an ID can be (base 10) */
    const USAFID_MAX = 2662511042;

    /**
    * Check if id is a correctly encoded with a lunh check digit.
    *
    * @param int the id being checked.
    * @return true a valid lunh encoded id.
    */
    private static function luhnChecksum($id)
    {
        $idChecksum = '';

        foreach (str_split(strrev((string) $id)) as $i => $d) {
            $idChecksum .= $i %2 !== 0 ? $d * 2 : $d;
        }

        return array_sum(str_split($idChecksum)) % 10 === 0;
    }

    /**
    * Encodes a number with a lunh check digit.
    * Using: ISO/IEC 7812-1
    * See: http://datagenetics.com/blog/july42013/index.html
    *
    * @param int an integer to be encoded
    * @return the integer with a luhn encoded check digit.
    */
    private static function luhn($number)
    {

        $stack = 0;
        $number = str_split(strrev($number));

        foreach ($number as $key => $value) {
            if ($key % 2 == 0) {
                $value = array_sum(str_split($value * 2));
            }
            $stack += $value;
        }
        $stack %= 10;

        if ($stack != 0) {
            $stack -= 10;
            $stack = abs($stack);
        }

        $number = implode('', array_reverse($number));
        $number = $number . strval($stack);

        return $number;
    }

    /**
    * Converts an integer (or record incerx) into a lunh encoded USAF_ID.
    *
    * @param int record number.
    * @return string the USAFB_ID
    */
    public static function getId($recordInumber)
    {
        $id = $recordInumber + self::USAFID_MIN - 1;
        $idLuhn = base_convert(self::luhn($id), 10, self::USAFID_BASE);
        return strtr($idLuhn, self::BASE_SYMBOLS, self::USAFID_SYMBOLS);
    }

    /**
    * Converts a USAF_ID to a integer (or record index).
    *
    * @param string the USAF_ID.
    * @return int record number.
    */
    public static function getRecordNo($usafId)
    {
        $idLong = strtr($usafId, self::USAFID_SYMBOLS, self::BASE_SYMBOLS);
        $idLunh = intval(base_convert($idLong, self::USAFID_BASE, 10));
        // luhn adds checksum as last digit, so will need to remove "intval($idLunh / 10)"
        return intval($idLunh / 10) - self::USAFID_MIN + 1;
    }

    /**
    * Checks the lynh encoded USAF_ID is valid.
    *
    * @param string the USAF_ID.
    * @return boolean true if is valid.
    */
    public static function isValidId($usafId)
    {
        $idLong = strtr($usafId, self::USAFID_SYMBOLS, self::BASE_SYMBOLS);
        $idLunh = base_convert($idLong, self::USAFID_BASE, 10);
        return self::luhnChecksum($idLunh);
    }
}
