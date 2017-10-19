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
     * @param $user User response from auth0
     *
     * @return array
     */
    public function transform(User $user)
    {
        $response = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'phone_number' => $user->getPhoneNumber(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
            'state' => $user->getState(),
            'postal_code' => $user->getPostalCode(),
            'roles' => $user->getRoles(),
            'email_verified' => (boolean) $user->getEmailVerified(),
            'picture' => $user->getPicture(),
            'status' => $user->getStatus(),
            'organization' => $user->getOrganization(),
            'address' => $user->getAddress(),
            'address2' => $user->getAddress2(),
            'updated_at' => $user->getUpdatedAt(),
            'created_at' => $user->getCreatedAt(),
            'last_login' => $user->getLastLogin()
        ];

        if (!is_null($user->getNickname())) {
            $response['nickname'] = $user->getNickname();
        }

        if (!is_null($user->getUpdatedBy())) {
            $response['updated_by'] = $user->getUpdatedBy();
        }

        if (!is_null($user->getCreatedBy())) {
            $response['created_by'] = $user->getCreatedBy();
        }

        return $response;
    }
}
