<?php

namespace App\Http\Controllers;

use Cache;
use DB;
use Queue;

use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Process\Process;

/**
 * Provide health and status checks for the application
 * This is used by kubernetes and monitoring software
 */
class StatusController extends Controller
{
    public function health()
    {
        return $this->getStatusApp() == 'OK'
            ? response()->json(['data' => 'OK'])
            : response()->json(['data' => 'System Unavailable. Check status.'], 503);
    }

    public function status()
    {
        $data = [
            'info' => [
                'sha'       => $this->getCommitHash(),
                'timestamp' => Carbon::now()->toW3cString(),
            ],
            'application_status' => $this->getStatusApp(),
            'db_status'          => $this->getStatusMongo(),
            'db2_status'         => $this->getStatusWDB(),
            'es_status'          => $this->getStatusEs(),
            'redis_status'       => $this->getStatusRedis(),
            'cache_status'       => $this->getStatusCache()
        ];
        return response()->json(compact('data'));
    }

    protected function getCommitHash()
    {
        if (!empty(env('GIT_DEPLOYED_SHA'))) {
            return env('GIT_DEPLOYED_SHA');
        }

        $process = new Process('git rev-parse --verify HEAD');
        $process->run();

        if (! $process->isSuccessful()) {
            return 'FAIL';
        }

        return trim($process->getOutput());
    }

    protected function getStatusApp()
    {
        return ($this->getStatusCache() == 'OK' &&
            $this->getStatusMongo() == 'OK' &&
                $this->getStatusWDB() == 'OK' &&
                    $this->getStatusEs() &&
                        $this->getStatusRedis() == 'OK')
            ? 'OK'
            : 'FAIL'
        ;
    }

    protected function getStatusCache()
    {
        try {
            $cache = Cache::store('redis');
            return 'OK';
        } catch (\Exception $e) {
            return 'FAIL';
        }
    }

    protected function getStatusMongo()
    {
        try {
            $mongo = DB::connection();
            return 'OK';
        } catch (\Exception $e) {
            return 'FAIL';
        }
    }

    protected function getStatusWDB()
    {
        try {
            $wdb = DB::connection('mysql-dw');
            return 'OK';
        } catch (\Exception $e) {
            return 'FAIL';
        }
    }

    protected function getStatusRedis()
    {
        try {
            $redis = Queue::getRedis();
            return 'OK';
        } catch (\Exception $e) {
            return 'FAIL';
        }
    }

    protected function getStatusEs()
    {
        try {
            $hosts = explode(',', env('ELASTICSEARCH_HOST', 'localhost'));
            $clientBuilder = ClientBuilder::create();
            $clientBuilder->setHosts($hosts);
            $client = $clientBuilder->build();
            return 'OK';
        } catch (\Exception $e) {
            return 'FAIL';
        }
    }
}
