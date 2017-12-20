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
     * Returns the coach registration records
     *
     * @return string (json) containing the Coach Registration resources limited to (per_page) results per-page/request
     */
    public function index(Request $request, $coachId)
    {
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
     * @return string (json) containing the Coach Registration resource OR corresponding error message
     */
    public function show(Request $request, $id, $coachId)
    {
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
                    'message' => 'Registration ('.$id.') not found for coach ('.$coach_id.').'
                ]
            ]);
        }
        
        return $this->respond('OK', $registration);
    }
}
