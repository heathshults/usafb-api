<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\User;
use App\Helpers\Constants;

class UserTransformer extends TransformerAbstract
{

    /**
     * Returns the user response based on an user array
     *
     * @param $user User
     *
     * @return array
     */
    public function transform(User $user)
    {
        $response = [
            'id' => $user->id,
            'id_external' => $user->id_external,
            'role_id' => $user->role_id,
            'role_name' => $user->role_name,
            'role_permissions' => $user->role_permissions,
            'name_first' => $user->name_first,
            'name_last' => $user->name_last,
            'phone' => $user->phone,
            'email' => $user->email,
            'address' => $user->address()
        ];
        return $response;
    }
}
