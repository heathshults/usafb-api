<?php

namespace App\Models;

use App\Models\Role;
use EloquentFilter\Filterable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use Log;
use Validator;

/**
* User collection model
*
* @package Models
*
*/

class BaseModel extends Eloquent
{
    protected $rules = [];
    protected $errors = [];
    
    public function errors()
    {
        return $this->errors;
    }
        
    public function valid()
    {
        $validator = Validator::make($this->attributes, $this->rules);
        if ($validator->fails()) {
            $this->errors = $validator->errors();
            return false;
        }
        return true;
    }
    
    public function errorMessages() : array
    {
        $errorMessages = [];
        if (!is_null($this->errors) && count($this->errors)) {
            $errorMessages = array_map(
                function ($obj) {
                    return implode(',', $obj);
                },
                array_values($this->errors->toArray())
            );
        }
        return $errorMessages;
    }
}
