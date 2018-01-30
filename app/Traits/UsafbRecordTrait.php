<?php

namespace App\Traits;

use App\Http\Services\IdService;
use App\Observers\UsafbRecordObserver;

trait UsafbRecordTrait
{
    public static function bootUsafbRecordTrait()
    {
        self::creating(function ($model) {
            $idService = new IdService();
            $newId = $idService->getNewId(Date('Y'), Date('m'));
            $model->id_usafb = $newId[1];
            $model->created_date = Date('Y-m-d');
            return true;
        });
        
        self::updating(function ($model) {
            $model->updated_date = Date('Y-m-d');
            return true;
        });
        
        static::observe(UsafbRecordObserver::class);
    }
}
