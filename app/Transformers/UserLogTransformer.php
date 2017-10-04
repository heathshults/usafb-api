<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\UserLog;
use App\Helpers\Constants;

class UserLogTransformer extends TransformerAbstract
{

    /**
     * Returns the user log response
     *
     * @param UserLog $log
     *
     * @return array
     */
    public function transform(UserLog $log)
    {
        return [
            'old_value' => $log->old_value,
            'new_value' => $log->new_value,
            'user' => $log->user_id,
            'data_field' => $log->data_field,
            'created_by' => $log->created_by,
            'created_at' => $log->created_at,
            'action' => $log->event_type
        ];
    }
}
