<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Config;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
 
class BaseCommand extends Command
{
    protected function lockProcess($expiresAt = null)
    {
        $lockKey = $this->lockKey();
        $lockedPid = Cache::get($lockKey);
        if (is_null($lockedPid)) {
            if (is_null($expiresAt)) {
                $expiresAt = Carbon::now()->addHours(1);
            }
            Cache::put($lockKey, getmypid(), $expiresAt);
            return true;
        } else {
            return false;
        }
    }
    
    protected function lockedPID()
    {
        $lockKey = $this->lockKey();
        $lockedPid = Cache::get($lockKey);
        return $lockedPid;
    }
    
    protected function unlockProcess()
    {
        $lockKey = $this->lockKey();
        Cache::forget($lockKey);
        return true;
    }
    
    protected function isLocked()
    {
        $lockKey = $this->lockKey();
        $lockedPid = Cache::get($lockKey);
        return !is_null($lockedPid);
    }
    
    protected function lockKey()
    {
        $key = get_class($this);
        $key = strtolower($key);
        $keyParts = explode('\\', $key);
        $key = $keyParts[count($keyParts)-1];
        return $key;
    }
}
