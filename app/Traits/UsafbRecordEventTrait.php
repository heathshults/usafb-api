<?php

namespace App\Traits;

use App\Observers\UsafbRecordObserver;

trait UsafbRecordEventTrait
{
    public static function bootUsafbRecordEventTrait()
    {
        static::observe(UsafbRecordObserver::class);
    }
}
