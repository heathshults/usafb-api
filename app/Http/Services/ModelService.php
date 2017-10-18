<?php

namespace App\Http\Services;

use App\Models\Coach;
use App\Models\CoachRegistration;
use App\Models\ParentGuardian;
use App\Models\Player;
use App\Models\PlayerRegistration;
use App\Models\Registration;
use App\Models\Registrant;
use App\Models\Source;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Services\ImportCsv\ImportCsvUtils;
use App\Helpers\FunctionalHelper;
use App\Helpers\DateHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * ModelService
 * Manage models
 *
 * @package    Http
 * @subpackage Services
 * @author     Matias Blanco <matias.blanco@bluestarsports.com>
 */
class ModelService
{

    const TYPE_PLAYER = 'PLAYER';
    const TYPE_COACH = 'COACH';
    const LEGAL_AGE = 18;
    protected $type;
    protected $fileLine;

    /**
     * Creates the entire DB model
     * @param string $type The type of the CSV (PLAYER, COACH)
     * @param string $sourceId The source id
     * @param array $columnNames Array of column names
     * @param arrat $fileLine Array of values
     */
    public function create($type, $sourceId, $fileLine)
    {
        $this->type = $type;
        $this->fileLine = $fileLine;

        $registrantIdAndUsafbId = $this->createRegistrant();
        $registrantId = $registrantIdAndUsafbId['id'];
        $registrationId = $this->createRegistration($sourceId, $registrantId);

        switch ($this->type) {
            case self::TYPE_PLAYER:
                $this->createPlayer($registrantId);
                $playerRegistrationId = $this->createPlayerRegistration($registrationId);
                $this->createParentGuardian($playerRegistrationId, $registrantId);
                break;
            case self::TYPE_COACH:
                $this->createCoach($registrantId);
                $this->createCoachRegistration($registrationId);
                break;
        }

        return $registrantIdAndUsafbId['usafb_id'];
    }
    /**
     * Creates the Registrant
     * @return int registrant id
     */
    public function createRegistrant()
    {
        $columns = $this->getTableColumns('registrant');
        $data = array_intersect_key($this->fileLine, $columns);
        return Registrant::insert($this->type, $data);
    }

    /**
     * Creates the Registration
     * @param int $sourceId The Source id
     * @param int $registrantId The Registrant id
     * @return int registration id
     */
    public function createRegistration($sourceId, $registrantId)
    {
        $columns = $this->getTableColumns('registration');
        $dataRegistrant = array_intersect_key($this->fileLine, $columns);

        foreach ($this->fileLine['registrations'] as $key => $registration) {
            $dataRegistration = array_intersect_key($registration, $columns);
            $data = array_merge($dataRegistrant, $dataRegistration);

            $regId = Registration::insert($this->type, $sourceId, $registrantId, $data);
        }

        /*
         * TODO: Change the registration return id logic
         * when we save more than one per player in the same request
         * Right now we'll assume that the registrations array contains only one.
         */
        return $regId;
    }

    /**
     * Creates the Player
     * @param int $registrantId The Registrant id
     * @return int The Player id
     */
    public function createPlayer($registrantId)
    {
        $columns = $this->getTableColumns('player');
        $data = array_intersect_key($this->fileLine, $columns);
        return Player::insert($registrantId, $data);
    }

    /**
     * Creates the Coach
     * @param int $registrantId The Registrant id
     * @return int The Coach id
     */
    public function createCoach($registrantId)
    {
        $columns = $this->getTableColumns('coach');
        $data = array_intersect_key($this->fileLine, $columns);
        return Coach::insert($registrantId, $data);
    }

    /**
     * Creates the player registration
     * @param int $registrationId The Registration id
     * @return int The PlayerRegistration id
     */
    public function createPlayerRegistration($registrationId)
    {
        $columns = $this->getTableColumns('player_registration');
        $data = array_intersect_key(array_merge($this->fileLine, $this->fileLine['registrations'][0]), $columns);
        return PlayerRegistration::insert($registrationId, $data);
    }

    /**
     * Creates the parent guardian
     * @param int $playerRegistrationId The PlayerRegistration id
     */
    public function createParentGuardian($playerRegistrationId)
    {
        $parentGuardianModels = [];
        $isMinor = DateHelper::getYearsFromDateToNow($this->fileLine['birth_date']) < self::LEGAL_AGE;

        $data = [];
        $columns = $this->getTableColumns('parent_guardian');
        foreach ($this->fileLine['guardians'] as $key => $guardian) {
            $guardianToSave = array_intersect_key(array_filter($guardian), $columns);
            if (!empty($guardianToSave)) {
                $data[] = $guardianToSave;
            }
        }

        if (!empty($data)) {
            // Save relationship
            ParentGuardian::insert($playerRegistrationId, $data);
        } elseif ($isMinor) {
            throw new BadRequestHttpException('The minor should have at least one '.
                                              'parent/guardian contact information.');
        }
    }

    /**
     * Creates the coach registration
     * @param int $registrationId The Registration id
     * @return int The CoachRegistration id
     */
    public function createCoachRegistration($registrationId)
    {
        $columns = $this->getTableColumns('coach_registration');
        $data = array_intersect_key($this->fileLine, $columns);
        return CoachRegistration::insert($registrationId, $data);
    }

    public function getTableColumns($table)
    {
        $tableFields = array_flip(DB::getSchemaBuilder()->getColumnListing($table));
        unset($tableFields['id']);

        return $tableFields;
    }
}
