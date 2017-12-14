<?php

namespace App\Http\Controllers;

use League\fractalService\Pagination\IlluminatePaginatorAdapter;
use League\fractalService\Manager;
use League\fractalService\Resource\Collection;
use League\fractalService\Resource\Item;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Coach;
use App\Models\CoachRegistration;

class CoachesController extends Controller
{

    /**
     * Returns the coach records
     *
     * @return string[] (json) containing the Coach resources limited to 50 results per-page/request
     */
    public function index(Request $request)
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

        $coaches = Coach::orderBy($sort['column'], $sort['order'])->paginate(50);        
        return response()->json($coaches);
    }

    /**
     * Returns the coach record with the specified ID
     *
     * @return string (json) containing the Coach resource OR corresponding error message
     */
    public function show($id)
    {
        $coach = Coach::find($id);
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Coach ('.$id.') not found.']]);
        }
        return $this->respond('OK', $coach);
    }
    
    public function search()
    {
        // TODO implement Elasticsearch
        return response()->json(array('OK' => 'ok'));
    }
        
    /**
     * Updates the player record with the specified ID
     *
     * @return string (json) containing the updated Player resource OR corresponding error message
     */    
    public function update(Request $request, $id)
    {
        $coach = Coach::find($id);
        if (!isset($coach)) {
            // return error
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$id.') not found.']]);            
        }
        $data = $request->all();
        if (isset($data) && sizeof($data) > 0) {
            // TODO validate
            if ($coach->update($data)) {
                return $this->respond('ACCEPTED', $coach);
            }
        }
        return $this->respond('NOT_MODIFIED', $coach);
    }
    
    /**
     * Removes the coach record and all of it's associations with the specified ID
     *
     * @return null
     */    
    public function destroy($id)
    {
        $coach = Coach::find($id);
        if (!isset($coach)) {
            // return error
            return $this->respond('NOT_FOUND', ['error' => ['message' => 'Player ('.$id.') not found.']]);            
        }
        $coach->delete();
        return $this->respond('OK', $coach);        
    }        
}
