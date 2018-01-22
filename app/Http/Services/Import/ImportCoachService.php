<?php

namespace App\Http\Services\Import;

use App\Models\Coach;
use Pitpit\Component\Diff\DiffEngine;
use Illuminate\Support\Facades\Log;

/**
 * ImportCoachService
 * Import Coach records
 *
 * @package    Http
 * @subpackage Services
 */

class ImportCoachService extends ImportService
{
    const SOURCE_NUM_MAX_RECORDS = 2500;
        
    const SOURCE_NUM_COLUMNS = 22;
        
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
        'Organization Name' => [
            'field' => 'organization_name'
        ],
        'Organization State' => [
            'field' => 'organization_state'
        ],
        'Years Experience' => [ 
            'type' => 'number', 
            'field' => 'years_experience' 
        ],
        'Positions' => [ 
            'type' => 'array', 
            'field' => 'positions' 
        ],
        'Level' => [ 
            'field' => 'level' 
        ],
        'Level Type' => [ 
            'field' => 'level_type' 
        ]
    ];

    // return new record/object (what we're importing)    
    public function newRecord() 
    {
        return (new Coach());
    } 
}
