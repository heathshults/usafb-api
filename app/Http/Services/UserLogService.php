<?php

namespace App\Http\Services;

use App\Models\UserLog;
use App\Models\Enums\LogEvent;
use Pitpit\Component\Diff\DiffEngine;
use App\Helpers\AuthHelper;

/**
 * UserLogService
 * Manage user change logs
 *
 * @package    Http
 * @subpackage Services
 * @author     Daylen Barban <daylen.barban@bluestarsports.com>
 */
class UserLogService
{
    protected $diffEngine;

    const UPDATED_FIELDS_NOT_LOG = [
        'updatedAt',
        'createdAt',
        'updatedBy',
        'createdBy'
    ];

    /**
     * Initialize user log service
     *
     * @constructor
     */
    public function __construct()
    {
        $this->diffEngine = new DiffEngine();
    }

    /**
     * Get paginated list of logs of a user sorted by creation date
     *
     * @param string $userId
     * @param array $itemsPerPage amount of items per page
     *
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedList($userId, $itemsPerPage)
    {
        return UserLog::where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($itemsPerPage);
    }

    /**
     * Create logs
     *
     * @param string $action LogEvent type
     * @param App\Models\User $admin Logged user
     * @param App\Models\User $newUser Created/Updated user
     * @param App\Models\User $oldUser when updates, refers to the previous user information
     *
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function create($action, $admin, $newUser, $oldUser = null)
    {
        if ($action === LogEvent::CREATE) {
            return UserLog::create(
                $this->generatelogInfo(
                    $newUser->getId(),
                    $action,
                    $admin
                )
            );
        }
        $logs = $this->generateLogsFromUpdate($admin, $oldUser, $newUser);
        return UserLog::insert(
            $logs
        );
    }

    /**
     * Create a log
     *
     * @param string $action LogEvent type
     * @param App\Models\User $admin Logged user
     * @param App\Models\User $newUser Created/Updated user
     * @param App\Models\User $oldUser when updates, refers to the previous user information
     *
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function generateLogsFromUpdate($admin, $oldUser, $newUser)
    {
        $diff = $this->diffEngine->compare($oldUser, $newUser);
        $logs = [];
        foreach ($diff as $element) {
            $elementName = $element->getIdentifier();
            if ($element->isModified() && !in_array($elementName, self::UPDATED_FIELDS_NOT_LOG)) {
                $logs[] = $this->generatelogInfo(
                    $newUser->getId(),
                    LogEvent::UPDATE,
                    $admin,
                    AuthHelper::getPropertyLabel($elementName),
                    $element->getOld(),
                    $element->getNew()
                );
            }
        }
        return $logs;
    }

    /**
     * Return Log info in array
     *
     * @param string $userId
     * @param string $action LogEvent type
     * @param App\Models\User $admin Logged user
     * @param string $dataField user field modified
     * @param string $oldValue previous value of user field modified
     * @param string $newValue new value of user field modified
     *
     * @return array log info
     */
    public function generatelogInfo(
        $userId,
        $action,
        $admin,
        $dataField = null,
        $oldValue = null,
        $newValue = null
    ) {
        return [
            'user_id' => $userId,
            'event_type' => $action,
            'data_field' => $dataField,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'created_by' => $admin->getFirstName()." ".$admin->getLastName(),
            'created_at' => date('Y/m/d H:i:s'),
            'created_by_id' => $admin->getId()
        ];
    }

    /**
     * Set DiffEngine service
     *
     * @param Pitpit\Component\Diff\DiffEngine $diffEngine
     *
     * @return void
     */
    public function setDiffEngine($diffEngine)
    {
        $this->diffEngine =  $diffEngine;
    }
}
