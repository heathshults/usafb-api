<?php

namespace App\Helpers;

class HttpHelper
{
    /**
     * Extract sort parameters for query from an expression
     *
     * @param string $sortExpression
     *
     * @return array [column (field name), order (ASC or DESC), symbol (1, -1)]
     */
    public static function extractSortParams($sortExpression)
    {
        $field = trim($sortExpression);
        $symbol = 1;
        $order = 'asc';
        $firstCharacter = $field[0];

        if ($firstCharacter == '-' || $firstCharacter == '+') {
            $field = substr($field, 1);
            $symbol = intval($firstCharacter.'1');
            if ($firstCharacter == '-') {
                $order = 'desc';
            }
        }
        return [
            'column' => $field,
            'order' => $order,
            'symbol' => $symbol
        ];
    }
}
