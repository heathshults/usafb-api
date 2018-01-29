<?php

namespace App\Traits;

use App\Observers\ElasticsearchObserver;

trait ElasticsearchTrait
{
    public static function bootElasticsearchTrait()
    {
        static::observe(ElasticsearchObserver::class);
    }

    public function searchIndex()
    {
        return $this->getTable();
    }

    public function searchType()
    {
        return $this->getTable();
    }

    public function searchContent()
    {
        return $this->searchContent();
    }
}
