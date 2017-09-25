<?php

namespace App\Helpers;

class FileHelper
{

    /**
     * Creates a temp file
     *
     * @param string      $name        File name
     * @param string      $permissions File permissions
     * @param json|string $content     File content
     *
     * @return File path
     */
    public static function createTempFile($name, $content)
    {
        $filePath = tempnam(sys_get_temp_dir(), $name);
        $file = fopen($filePath, 'w+');
        fwrite($file, json_encode($content));
        return $filePath;
    }
}
