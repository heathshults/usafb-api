<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Jenssegers\Mongodb\Eloquent\Builder;
use EloquentFilter\Filterable;
use Illuminate\Support\Arr;
use League\Csv\Writer;
use Log;

/**
* Import collection model
*
* @package Models
*
*/

class Import extends Eloquent
{
    // Two different types of exports (coaches and players)
    const TYPE_COACHES = 'coaches';
    const TYPE_PLAYERS = 'players';
    
    // State/statuses that export can be in (only one at a time - default PENDING)
    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_FAILED = -1;

    // Disable soft deletes for now...
    protected $connection = 'mongodb';
    protected $table = 'imports';
    protected $dates = [ 'created_at' ];
        
    // Defaults
    protected $attributes = [
        'status' => Import::STATUS_PENDING,
        'num_records' => 0,
        'num_imported' => 0,
        'num_errors' => 0,
        'results' => [],
        'errors' => []
    ];

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'status_details',
        'file_name',
        'file_path_remote',
        'num_records',
        'num_imported',
        'num_errors',
        'results',
        'errors',
        'created_at',
    ];
    
    public function scopePending($query)
    {
        return $query->where([ 'status' => Import::STATUS_PENDING ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where([ 'status' => Import::STATUS_COMPLETED ]);
    }

    public function scopeFailed($query)
    {
        return $query->where([ 'status' => Import::STATUS_FAILED ]);
    }
    
    public function scopePlayers($query)
    {
        return $query->where([ 'type' => Import::TYPE_PLAYERS ]);
    }

    public function scopeCoaches($query)
    {
        return $query->where([ 'type' => Import::TYPE_COACHES ]);
    }
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    
    public function getFilePathByType($fileType)
    {
        if ($fileType == 'source') {
            return $this->file_path_source;
        } elseif ($fileType == 'results') {
            return $this->file_path_result;
        } elseif ($fileType == 'errors') {
            return $this->file_path_error;
        }
        return null;
    }
    
    public function resultsToCSV()
    {
        if (is_null($this->results)) {
            return null;
        }
        $stream = fopen('php://memory', 'r+');
        $csvWriter = Writer::createFromStream($stream);
        foreach ($this->results as $result) {
            $csvWriter->insertOne([
                $result['row'],
                $result['id'],
                $result['id_external'],
                $result['id_usafb'],
                $result['name_first'],
                $result['name_middle'],
                $result['name_last'],
            ]);
        }
        $csvContent = $csvWriter->getContent();
        return $csvContent;
    }
    
    public function errorsToCSV()
    {
        if (is_null($this->errors)) {
            return null;
        }
        $stream = fopen('php://memory', 'r+');
        $csvWriter = Writer::createFromStream($stream);
        foreach ($this->errors as $error) {
            $csvWriter->insertOne([
                $error['row'],
                $error['errors'],
            ]);
        }
        $csvContent = $csvWriter->getContent();
        return $csvContent;
    }
}
