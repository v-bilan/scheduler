<?php

namespace App\Services;

use App\Util\Date;
use App\Entity\Role;
use App\Models\TaskWitnessDate;
use App\Models\Witness;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class TaskManager
{
    /**
     * Create a new class instance.
     */

    private $witnessesByRole  = [];
    private $witnessesByRoleSorted  = [];

    private $usedWitnesses = [];

    public function __construct(private TasksParser $tasksParser) {}

    private function getWitnessesByRoleSorted(string $role, Date $date)
    {
        if (!isset($this->witnessesByRoleSorted[$date->__toString()][$role])) {
            $this->witnessesByRoleSorted[$date->__toString()][$role] = $this->getWitnessesByRole($role, $date)->toArray();
            uasort($this->witnessesByRoleSorted[$date->__toString()][$role], fn($a, $b) => $a->full_name > $b->full_name);
        }
        return $this->witnessesByRoleSorted[$date->__toString()][$role];
    }
    private function getWitnessesByRole(string $role, Date $date)
    {
        if (!isset($this->witnessesByRole[$date->__toString()][$role])) {
            $this->witnessesByRole[$date->__toString()][$role] = DB::table('witnesses')->select([
                'witnesses.id as witness_id',
                'witnesses.full_name',
                'roles.name as role_name',
                'roles.id as role_id',
                DB::raw('max(task_witness_date.date) as last_date')
            ])
                ->join('role_witness', 'witnesses.id', '=', 'role_witness.witness_id')
                ->join('roles', 'role_witness.role_id', '=', 'roles.id')
                ->leftJoin('task_witness_date', function (JoinClause $join) use ($date) {
                    $join->on('witnesses.id', '=', 'task_witness_date.witness_id')
                        ->on('roles.id', '=', 'task_witness_date.role_id')
                        ->where('task_witness_date.date', '<', $date);
                })
                ->where('roles.name', '=', $role)
                ->where('witnesses.active', '=', 1)
                ->groupBy(['witnesses.full_name', 'roles.id', 'roles.name', 'witnesses.id'])
                ->orderBy('last_date')
                ->get();
        }


        return $this->witnessesByRole[$date->__toString()][$role];
    }

    public function createSchedule(array $witnesses, Date $date)
    {
        $tasks = $this->getTasksData($date, false);
        try {
            DB::beginTransaction();
            TaskWitnessDate::where('date', '=', $date)->delete();
            foreach ($tasks as $taskName => $taskData) {
                TaskWitnessDate::create([
                    'role_id' => $taskData['role_id'],
                    'task' => $taskName,
                    'witness_id' => $witnesses[$taskName],
                    'date' => $date
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            report($e);
            DB::rollBack();
            return false;
        }
        return true;
    }

    private function combine($rawTasks, $dbTasks): array
    {
        foreach ($dbTasks as $task) {
            $rawTasks[$task->task]['witness'] = $task->witness;
        }
        return $rawTasks;
    }


    public function getTasksData(Date $date, $withWitnesses = true): array
    {
        $year = $date->getFullYear();
        $week = $date->getWeek();

        $rawTasks = $this->getTasksDataFromTasks($this->tasksParser->getTasks($year, $week));

        $dbTasks = TaskWitnessDate::with('witness')->where('date', '=',  $date)->get();

        $roles = Role::orderBy('priority', 'DESC')->get()->keyBy('name')->toArray();

        $tasks = $this->combine($rawTasks, $dbTasks);


        $number = 1;

        foreach ($tasks as &$task) {
            $task['number'] = $number++;
            $task['priority'] = $roles[$task['role']]['priority'] ?? 0;
            $task['role_id'] = $roles[$task['role']]['id'] ?? 0;
        }

        if ($withWitnesses) {
            uasort($tasks, fn($a, $b) => $a['priority'] < $b['priority']);

            $this->usedWitnesses[$date->__toString()] = [];
            foreach ($tasks as &$task) {

                $task['witnesses'] = $this->getWitnessesByRoleSorted($task['role'], $date);
                //if (isset($task['witness'])) continue;
                $task['suggested_witness'] = $this->getNexWitnessByRole($task['role'], $date)->current();
            }


            uasort($tasks, fn($a, $b) => $a['number'] > $b['number']);
        }

        return $tasks;
    }

    public function getDateString(int $year, int $week)
    {
        return $this->tasksParser->getDate($year, $week);
    }

    private function getTasksDataFromTasks(array $tasks): array
    {
        $result = [
            'leader' => [
                'label' => 'Ведучий',
                'role' => 'Ведучий'
            ],
            'first_pray' => [
                'label' => 'Початкова молитва',
                'role' => 'Молитва'
            ],
        ];

        foreach ($tasks as $key => $task) {
            $result['task' . ($key + 1)] = [
                'label' => $task,
                'role' => $this->getRoleNameByTaskName($task)
            ];
        }
        $result['reader'] = [
            'label' => 'Читець',
            'role' => 'Читець'
        ];
        $result['last_pray'] = [
            'label' => 'Кінцева молитва',
            'role' => 'Молитва'
        ];

        return $result;
    }

    private function getNexWitnessByRole(string $role,  Date $date)
    {
        $witnesses = $this->getWitnessesByRole($role, $date);
        foreach ($witnesses as $witness) {
            if (isset($this->usedWitnesses[$date->__toString()][$witness->witness_id])) continue;
            $this->usedWitnesses[$date->__toString()][$witness->witness_id] = $witness->witness_id;
            yield $witness;
        }
    }

    private function getRoleNameByTaskName(string $taskName): string
    {
        if (str_starts_with($taskName, '1. ')) return 'Промова';
        if (str_contains($taskName, 'Промова.')) {
            return 'Учнівська Промова';
        }
        return match (trim($taskName, '1234567890. ')) {
            'Місцеві потреби' => 'Старійшина',
            'Вивчення Біблії у зборі' => 'Вивченя Біблії',
            'Читання Біблії' => 'Читання Біблії',
            'Промова' => 'Учнівська Промова',
            'Починаємо розмову' => 'Школа',
            'Розвиваємо інтерес' => 'Школа',
            'Пояснюємо свої переконання' => 'Школа',
            'Підготовка учнів'  => 'Школа',
            default => 'Призначений брат'
        };
    }
}
