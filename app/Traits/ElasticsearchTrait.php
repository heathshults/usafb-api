<?php

namespace App\Traits;

use App\Observers\ElasticsearchObserver;

trait ElasticsearchTrait
{
    public static function bootElasticsearchTrait()
    {
        static::observe(ElasticsearchObserver::class);
    }

    public function getSearchIndex()
    {
        return $this->getTable();
    }

    public function getSearchType()
    {
        return $this->getTable();
    }

    public function toSearchBody()
    {
        return $this->toSearchBody();
    }
}
