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
     * @return string[] (json) containing the Coach Registration resources limited to 50 results per-page/request
     */
    public function index(Request $request, $coach_id)
    {
        $pagination = $this->buildPaginationCriteria($request->query());
        $queryFilter = $request->only('filter');
        $filters = !is_null($queryFilter['filter']) ? $queryFilter['filter'] : [];                

        $sort = $this->buildSortCriteria($request->query());
        // default sort column/order
        if (is_null($sort)) {
            $sort = [
                'column' => 'created_at', 
                'order' => 'desc'
            ];         
        }

        // validate coach record
        $coach = Coach::find($coach_id);
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Coach ('.$id.') not found.']]);
        }

        // too bad $coach->registrations()->orderBy( ... ) doesn't work (no proper query interfaces in this driver)
        $registrations = CoachRegistration::where('coach_id', $coach->id)->orderBy($sort['column'], $sort['order'])->paginate();
        return $this->respond('OK', $registrations);
    }

    /**
     * Return the Coach Registration record for the specified Coach ID and Registration ID
     *
     * @return string (json) containing the Coach Registration resource OR corresponding error message
     */
    public function show(Request $request, $coach_id, $id) 
    {
        // lookup registration directly from coach registration collection
        $registration = CoachRegistration::findOne(['coach_id' => $coach_id, 'id' => $id]);
        if (is_null($registration)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Registration ('.$id.') not found for coach ('.$coach_id.').']]);          
        }
        return $this->respond('OK', $registration);        
    }
    
}
