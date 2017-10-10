<?php

namespace App\Models;

use App\Helpers\Constants;

class User
{
    protected $id;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $city;
    protected $postalCode;
    protected $password;
    protected $emailVerified;
    protected $status;
    protected $createdAt;
    protected $updatedAt;
    protected $createdBy;
    protected $updatedBy;
    protected $roles;
    protected $nickname;
    protected $phoneNumber;
    protected $state;
    protected $picture;
    protected $lastLogin;
    protected $country;
    protected $organization;

    /**
     * Initialize authentication client with auth credentials
     *
     * @param array $user Auth0 user response
     *
     * @constructor
     */
    public function __construct(array $user)
    {
        if (isset($user['email'])) {
            $this->email = $user['email'];
        }

        if (isset($user['email_verified'])) {
            $this->emailVerified = $user['email_verified'];
        }

        $this->status = !isset($user['blocked']) || !$user['blocked'] ?
        'Enabled' : 'Disabled';

        if (isset($user['picture'])) {
            $this->picture = $user['picture'];
        }

        if (isset($user['updated_at'])) {
            $this->updatedAt = $user['updated_at'];
        }

        if (isset($user['created_at'])) {
            $this->createdAt = $user['created_at'];
        }

        if (isset($user['user_id'])) {
            $this->id = $user['user_id'];
        } elseif (isset($user['sub'])) {
            $this->id = $user['sub'];
        }

        if (isset($user['nickname'])) {
            $this->nickname = $user['nickname'];
        }

        $userMetadata = isset($user['user_metadata']) ?
            $user['user_metadata'] : [];

        if (empty($userMetadata) && isset($user[getenv('AUTH_METADATA')])) {
            $userMetadata = $user[getenv('AUTH_METADATA')];
        }

        if (isset($userMetadata['country'])) {
            $this->country = $userMetadata['country'];
        }

        if (isset($userMetadata['first_name'])) {
            $this->firstName = $userMetadata['first_name'];
        }

        if (isset($userMetadata['last_name'])) {
            $this->lastName = $userMetadata['last_name'];
        }

        if (isset($userMetadata['phone_number'])) {
            $this->phoneNumber = $userMetadata['phone_number'];
        }

        if (isset($userMetadata['postal_code'])) {
            $this->postalCode = $userMetadata['postal_code'];
        }

        if (isset($userMetadata['state'])) {
            $this->state = $userMetadata['state'];
        }

        if (isset($userMetadata['city'])) {
            $this->city = $userMetadata['city'];
        }

        if (isset($userMetadata['organization'])) {
            $this->organization = $userMetadata['organization'];
        }

        if (isset($userMetadata['roles'])) {
            $this->roles = $userMetadata['roles'];
        }

        if (isset($userMetadata['updated_by'])) {
            $this->updatedBy = $userMetadata['updated_by'];
        }

        if (isset($userMetadata['created_by'])) {
            $this->createdBy = $userMetadata['created_by'];
        }

        if (isset($user['last_login'])) {
            $this->lastLogin = $user['last_login'];
        }
    }

    /**
     * Returns user id
     *
     * @return string id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns user email
     *
     * @return string email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns user email verified flag
     *
     * @return string email verified
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * Returns user state
     *
     * @return string state
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Returns user phone number
     *
     * @return string phone number
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Returns user postal code
     *
     * @return string postal code
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Returns user first name
     *
     * @return string first name
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Returns user last name
     *
     * @return string last name
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Returns user roles
     *
     * @return array roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns user picture url
     *
     * @return string picture url
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Returns user created at
     *
     * @return string creation at
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Returns user updated at
     *
     * @return string updated at
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Returns user created by
     *
     * @return string creation by
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Returns user updated by
     *
     * @return string updated by
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Returns user status
     *
     * @return string status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns user nickname
     *
     * @return string nickname
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Returns user city
     *
     * @return string city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Returns last login
     *
     * @return string last login
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Returns user country
     *
     * @return array country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Returns user organization
     *
     * @return array organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
