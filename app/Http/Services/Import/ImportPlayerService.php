<?php

namespace App\Http\Services\Import;

use App\Models\Player;
use Pitpit\Component\Diff\DiffEngine;
use Illuminate\Support\Facades\Log;

/**
 * ImportPlayerService
 * Import Player records
 *
 * @package    Http
 * @subpackage Services
 */

class ImportPlayerService extends ImportService
{
    const SOURCE_NUM_MAX_RECORDS = 2500;
        
    const SOURCE_NUM_COLUMNS = 32;
        
    const COLUMNS = [
        'External ID' => [
            'field' => 'id_external'
        ],
        'First Name' => [
            'field' => 'name_first'
        ],
        'Middle Name' => [
            'field' => 'name_middle'
        ],
        'Last Name' => [
            'field' => 'name_last'
        ],
        'Street 1' => [
            'field' => 'address.street_1'
        ],
        'Street 2' => [
            'field' => 'address.street_2'
        ],
        'City' => [
            'field' => 'address.city'
        ],
        'County' => [
            'field' => 'address.county'
        ],
        'State' => [
            'field' => 'address.state'
        ],
        'Postal Code' => [
            'field' => 'address.postal_code'
        ],
        'DOB' => [
            'field' => 'dob'
        ],
        'Gender' => [
            'field' => 'gender'
        ],
        'Height FT' => [
            'type' => 'number',
            'field' => 'height_ft'
        ],
        'Height IN' => [
            'type' => 'number',
            'field' => 'height_in'
        ],
        'Grade' => [
            'type' => 'number',
            'field' => 'grade'
        ],
        'Graduation Year' => [
            'type' => 'number',
            'field' => 'graduation_year'
        ],
        'Email' => [
            'field' => 'email'
        ],
        'Home Phone' => [
            'field' => 'phone_home'
        ],
        'Mobile Phone' => [
            'field' => 'phone_mobile'
        ],
        'Work Phone' => [
            'field' => 'phone_work'
        ],
        'Twitter' => [
            'field' => 'social_twitter'
        ],
        'Instagram' => [
            'field' => 'social_instagram'
        ],
        'Sports' => [
            'type' => 'array',
            'field' => 'sports'
        ],
        'Years Experience' => [
            'type' => 'number',
            'field' => 'years_experience'
        ],
        'Guardian - First Name' => [
            'field' => 'guardians.1.name_first'
        ],
        'Guardian - Last Name' => [
            'field' => 'guardians.1.name_last'
        ],
        'Guardian - Address Street 1' => [
            'field' => 'guardians.1.address.street_1'
        ],
        'Guardian - Address Street 2' => [
            'field' => 'guardians.1.address.street_2'
        ],
        'Guardian - Address City' => [
            'field' => 'guardians.1.address.city'
        ],
        'Guardian - Address County' => [
            'field' => 'guardians.1.address.county'
        ],
        'Guardian - Address State' => [
            'field' => 'guardians.1.address.state'
        ],
        'Guardian - Address Postal Code' => [
            'field' => 'guardians.1.address.postal_code'
        ],
    ];

    // return new record/object (what we're importing)
    public function newRecord()
    {
        return (new Player());
    }
}
