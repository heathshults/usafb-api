<?php

namespace App\Observers;

use App\Models\RecordEvent;
use Illuminate\Support\Facades\Log;

class UsafbRecordObserver
{
    public function __construct()
    {
        Log::debug("UsafbRecordObserver > construct(..)");
    }

    public function saved($model)
    {
        Log::debug('UsafbRecordObserver > saved(..)');
        $recordId = $model->id;
        $recordType = $model->getTable();
        $recordEvent = new RecordEvent([
            'record_id' => $recordId,
            'record_type' => $recordType,
        ]);
        $recordEvent->save();
        return true;
    }

    public function deleted($model)
    {
        Log::debug('UsafbRecordObserver > deleted(..)');
        $recordId = $model->id;
        $recordType = $model->getTable();
        $recordEvent = new RecordEvent([
            'record_id' => $recordId,
            'record_type' => $recordType,
            'deleted' => true,
        ]);
        $recordEvent->save();
        return true;
    }
}
