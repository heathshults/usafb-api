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

use App\Http\Services\Elasticsearch\ElasticsearchService;
use App\Http\Services\Elasticsearch\ElasticsearchCoachQuery;

class CoachesController extends Controller
{

    /**
     * Search for Coaches
     *
     * @return string[] (json) containing the Coach resources limited to 50 results per-page/request
     */
    public function search(Request $request)
    {
        $esQuery = new ElasticsearchCoachQuery($request->query());
        
        if (!$esQuery->isValid()) {
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Invalid search.'
                ]
            ]);
        }
        
        $es = new ElasticsearchService();
        $es->setQuery($esQuery);

        $sortCriteria = $this->buildSortCriteria($request->query());
        if (!is_null($sortCriteria)) {
            $es->setSearchSort($sortCriteria['column'], $sortCriteria['order']);
        }

        $paginationCriteria = $this->buildPaginationCriteria($request->query());
        $es->setSearchPageSize($paginationCriteria);
        
        $results = $es->searchCoaches($esQuery);
        return $this->respond('OK', $results->toArray());
    }

    /**
     * Returns the Coach record with the specified ID
     *
     * @return string (json) containing the Coach resource OR corresponding error message
     */
    public function show($id)
    {
        $coach = Coach::find($id);
        
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Coach ('.$id.') not found.'
                ]
            ]);
        }
        
        return $this->respond('OK', $coach);
    }
            
    /**
     * Updates the Coach record with the specified ID
     *
     * @return string (json) containing the updated Coach resource OR corresponding error message
     */
    public function update(Request $request, $id)
    {
        $coach = Coach::find($id);
        
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Coach ('.$id.') not found.'
                ]
            ]);
        }
        
        $data = $request->all();
        
        if (isset($data) && sizeof($data) > 0) {
            // loop through PUT fields and assign to model
            foreach ($data as $key => $value) {
                $coach->setAttribute($key, $value);
            }
            if ($coach->valid() && $coach->save()) {
                return $this->respond('ACCEPTED', $coach);
            } else {
                $errors = $coach->errors();
                return $this->respond('INVALID', [
                    'error' => [
                        'message' => 'Error updating Coach record.',
                        'errors' => $errors
                    ]
                ]);
            }
        }
        
        return $this->respond('NOT_MODIFIED', $coach);
    }
    
    /**
     * Removes the Coach record and all of it's associations with the specified ID
     *
     * @return null
     */
    public function destroy($id)
    {
        $coach = Coach::find($id);
        
        if (is_null($coach)) {
            return $this->respond('NOT_FOUND', [
                'error' => [
                    'message' => 'Coach ('.$id.') not found.'
                ]
            ]);
        }
        
        $coach->delete();
        
        return $this->respond('OK', $coach);
    }
    
    /**
     * Create a new Coach record
     *
     * @return string (json) containing the new Coach resource OR corresponding error message
     */
    public function create(Request $request)
    {
        $coach = new Coach($request->all());
        
        if ($coach->valid() && $coach->save()) {
            return $this->respond('CREATED', $coach);
        } else {
            $errors = $coach->errors();
            return $this->respond('INVALID', [
                'error' => [
                    'message' => 'Error creating Coach record.',
                    'errors' => $errors
                ]
            ]);
        }
    }
}
