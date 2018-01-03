<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\CoachRegistration;

use League\fractalService\Pagination\IlluminatePaginatorAdapter;
use League\fractalService\Manager;
use League\fractalService\Resource\Collection;
use League\fractalService\Resource\Item;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CoachRegistrationsController extends Controller
{

    /**
     * Returns the Coach Registration records for (coachId)
     *
     * @param Request $request
     * @param string $coachId
     *
     * @return string (json) containing the Coach Registration resources limited to (per_page) results per-page/request
     */
    public function index(Request $request, $coachId)
    {
        // find Coach record (or error)
        $coach = Coach::find($coachId);
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Coach ('.$coachId.') not found.'
                ]
            ]);
        }

        $sort = $this->buildSortCriteria($request->query(), [ 'column' => 'created_at', 'order' => 'desc' ]);
        $registrations = $coach->registrations()->orderBy($sort['column'], $sort['order'])->all();
        
        return $this->respond('OK', $registrations);
    }

    /**
     * Return the Coach Registration record for the specified Coach ID and Registration ID
     *
     * @param Request $request
     * @param string $id
     * @param string $coachId
     *
     * @return string (json) containing the Coach Registration resource OR corresponding error message
     */
    public function show(Request $request, $id, $coachId)
    {
        // find Coach record (or error)
        $coach = Coach::find($coachId);
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Coach ('.$coachId.') not found.'
                ]
            ]);
        }
        
        // lookup registration directly from coach registration collection
        $registration = $coach->registrations()->find($id);
        if (is_null($registration)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Registration ('.$id.') not found for Coach ('.$coach_id.').'
                ]
            ]);
        }
        
        return $this->respond('OK', $registration);
    }
    
    /**
     * Create a new Registration record
     *
     * @param Request $request
     * @param string $id
     *
     * @return string (json) containing new record resource OR corresponding error message
     */
    public function create(Request $request, $id)
    {
        // find Coach record (or error)
        $coach = Coach::find($id);
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Coach ('.$id.') not found.'
                ]
            ]);
        }
        
        // instantiate new coach registraiton and validate fields
        $coachRegistration = new CoachRegistration($request->all());
        if (!$coachRegistration->valid()) {
            $errors = $coachRegistration->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating Registration record.',
                    'errors' => $errors
                ]
            ]);
        }

        // set all past registrations current to false - change later to use indexed find/update
        foreach ($coach->registrations()->where('current', true)->all() as $registration) {
            $registration->current = false;
            $registration->save();
        }
        
        // associate new embedded registration with Coach record
        $coach->registrations()->associate($coachRegistration);

        // do final Coach validation (with combined data) and save
        if ($coach->valid() && $coach->save()) {
            return $this->respond('ACCEPTED', $coachRegistration);
        } else {
            $errors = $coach->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating Registration record.',
                    'errors' => $errors
                ]
            ]);
        }
    }
    
    /**
     * Update Registration record
     *
     * @param Request $request
     * @param string $id
     * @param string $coachId
     *
     * @return string (json) containing new record resource OR corresponding error message
     */
    public function update(Request $request, $id, $coachId)
    {
        // find Coach record (or error)
        $coach = Coach::find($coachId);
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Coach ('.$coachId.') not found.'
                ]
            ]);
        }
        
        // find Registration record (or error)
        $registration = $coach->registrations()->find($id);
        if (is_null($registration)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Registration ('.$id.') not found for Coach ('.$coach_id.').'
                ]
            ]);
        }
        
        $requestJson = $request->json()->all();
        
        // merge/fill registration object with json values
        $registration->fill($requestJson);
        
        // validate merged record and save (or error)
        if ($registration->valid() && $registration->save()) {
            return $this->respond('OK', $registration);
        } else {
            $errors = $registration->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error updating Registration record.',
                    'errors' => $errors
                ]
            ]);
        }
    }
}
