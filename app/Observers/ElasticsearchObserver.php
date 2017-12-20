<?php

namespace App\Observers;

use Illuminate\Support\Facades\Log;

use App\Http\Services\Elasticsearch\ElasticsearchService;
use App\Exceptions\ElasticsearchException;

class ElasticsearchObserver
{
    private $service;

    public function __construct()
    {
        Log::debug("ElasticsearchObserver > construct(..)");
        $this->service = new ElasticsearchService();
    }

    public function saved($model)
    {
        Log::debug('ElasticsearchObserver > saved(..)');
        Log::debug('ElasticsearchObserver > saved(..) model index: '.$model->getSearchIndex());
        Log::debug('ElasticsearchObserver > saved(..) model type: '.$model->getSearchType());
        Log::debug('ElasticsearchObserver > saved(..) model id: '.$model->id);
        try {
            Log::debug('ElasticsearchObserver > saved(..) indexing document.');
            $response = $this->service->indexDocument(
                $model->getSearchIndex(),
                $model->getSearchType(),
                $model->id,
                $model->toSearchBody()
            );
            Log::debug('ElasticsearchObserver > saved(..) index response ('.$response.')');
            return $response;
        } catch (ElasticsearchException $ex) {
            Log::debug('ElasticsearchObserver > saved(..) Exception: '.$ex->getMessage());
            return false;
        }
    }

    public function deleted($model)
    {
        Log::debug('ElasticsearchObserver > deleted(..) model id: '.$model->id);
        try {
            $response = $this->service->deleteDocument(
                $model->getSearchIndex(),
                $model->getSearchType(),
                $model->id
            );
            Log::debug('ElasticsearchObserver > deleted(..) index response ('.$response.')');
            return $response;
        } catch (ElasticsearchException $ex) {
            Log::debug('ElasticsearchObserver > deleted(..) Exception: '.$ex->getMessage());
            return false;
        }
    }
}
