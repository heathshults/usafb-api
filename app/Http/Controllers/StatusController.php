<?php

namespace App\Http\Controllers;

use Cache;
use DB;
use Queue;

use Carbon\Carbon;
use Symfony\Component\Process\Process;

class StatusController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function health()
    {
        return $this->getStatusApp() == 'OK'
            ? response()->json(['data' => 'OK'])
            : response()->json(['data' => 'System Unavailable. Check status.'], 503);
        ;
    }

    public function status()
    {
        $data = [
            'info' => [
                'sha'       => $this->getCommitHash(),
                'timestamp' => Carbon::now()->toW3cString(),
            ],
            'application_status' => $this->getStatusDB(),
           # 'application_status' => $this->getStatusApp(),
            'db_status'          => $this->getStatusDB(),
           # 'redis_status'       => $this->getStatusRedis(),
           # 'cache_status'       => $this->getStatusCache(),
        ];

        return response()->json(compact('data'));
    }

    protected function getCommitHash()
    {
        $process = new Process('git rev-parse --verify HEAD');
        $process->run();

        if (! $process->isSuccessful()) {
            return 'FAIL';
        }

        return trim($process->getOutput());
    }

    protected function getStatusApp()
    {
        return ($this->getStatusCache() == 'OK' && $this->getStatusDB() == 'OK' && $this->getStatusRedis() == 'OK')
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

    protected function getStatusDB()
    {
        try {
            $pdo = DB::connection()->getPdo();
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
}
