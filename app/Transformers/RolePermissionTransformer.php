<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Role;
use App\Helpers\Constants;

class RolePermissionTransformer extends TransformerAbstract
{

    /**
     * Returns the role permission response based on a string
     *
     * @param $permission string response
     *
     * @return array
     */
    public function transform(string $permission)
    {
        $response = [
            'permission' => $permission
        ];
        return $response;
    }
}
