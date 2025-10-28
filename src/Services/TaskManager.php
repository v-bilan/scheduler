<?php

namespace App\Services;

use App\Util\Date;
use App\Entity\Role;
use App\Entity\TaskWitnessDate;
use App\Entity\Witness;
use App\Repository\RoleRepository;
use App\Repository\TaskWitnessDateRepository;
use App\Repository\WitnessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class TaskManager
{
    /**
     * Create a new class instance.
     */

    private $witnessesByRole  = [];
    private $witnessesByRoleSorted  = [];

    private $usedWitnesses = [];

    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private TasksParser $tasksParser,
        private WitnessRepository $witnessRepository,
        private RoleRepository $roleRepository,
        private TaskWitnessDateRepository $taskWitnessDateRepository
    ) {}

    private function getWitnessesByRoleSorted(string $role, Date $date)
    {
        $key = (string)$date;
        if (!isset($this->witnessesByRoleSorted[$key][$role])) {
            $this->witnessesByRoleSorted[$key][$role] = $this->getWitnessesByRole($role, $date);
            uasort($this->witnessesByRoleSorted[$key][$role], fn($a, $b) => $a['fullName'] > $b['fullName']);
        }
        return $this->witnessesByRoleSorted[$key][$role];
    }
    private function getWitnessesByRole(string $role, Date $date)
    {
        $key = (string)$date;
        if (!isset($this->witnessesByRole[$key][$role])) {
            $this->witnessesByRole[$key][$role] = $this->witnessRepository->getWitnessesByRole($role, $date);
        }
        return $this->witnessesByRole[$key][$role];
    }

    public function createSchedule(array $witnesses, Date $date, ?bool $school = null)
    {
        try {
            $this->em->wrapInTransaction(function ($em) use ($witnesses, $date, $school) {
                $tasks = $this->getTasksData(date: $date, withWitnesses: false, school: $school);

                $taskWitnessDates = $this->taskWitnessDateRepository->findBy(['date' => $date]);

                foreach ($taskWitnessDates as $taskWitnessDate) {
                    if ($school === null || $taskWitnessDate->getRole()->isSchool() == $school) {
                        $em->remove($taskWitnessDate);
                    }
                }
                $em->flush();

                foreach ($tasks as $taskName => $taskData) {
                    $taskWitnessDate = new TaskWitnessDate();
                    $taskWitnessDate->setDate($date);
                    $taskWitnessDate->setTask($taskName);
                    $taskWitnessDate->setRole($this->roleRepository->find($taskData['role_id']));
                    $taskWitnessDate->setWitness($this->witnessRepository->find($witnesses[$taskName]));
                    $em->persist($taskWitnessDate);
                }
                $em->flush();
            });
            return true;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    private function combine($rawTasks, $dbTasks): array
    {
        foreach ($dbTasks as $task) {
            $rawTasks[$task->getTask()]['witness'] = $task->getWitness();
        }
        return $rawTasks;
    }

    private function getArraYwitkKey(array $data, $keyField): array
    {
        $method = 'get' . ucfirst($keyField);
        $result = [];
        foreach ($data as $value) {
            $result[$value->{$method}()] = $value;
        }
        return $result;
    }

    public function getTasksData(Date $date, $withWitnesses = true, ?bool $school = null): array
    {
        //   $school  = !true;
        $year = $date->getFullYear();
        $week = $date->getWeek();

        $rawTasks = $this->getTasksDataFromTasks($this->tasksParser->getTasks($year, $week));

        $dbTasks = $this->taskWitnessDateRepository->getWithWitness($date);
        // TaskWitnessDate::with('witness')->where('date', '=',  $date)->get();

        $roles = $this->getArraYwitkKey($this->roleRepository->findBy([], ['priority' => 'DESC']), 'name');
        // Role::orderBy('priority', 'DESC')->get()->keyBy('name')->toArray();

        $tasks = $this->combine($rawTasks, $dbTasks);
        //  dd($rawTasks, $dbTasks, $tasks, $roles);

        $number = 1;

        foreach ($tasks as $key => &$task) {
            $role = $roles[$task['role']] ?? null;

            if ($school !== null && $role->isSchool() != $school) {
                unset($tasks[$key]);
                continue;
            }
            $task['number'] = $number++;
            $task['priority'] = $role?->getPriority() ?? 0;
            $task['role_id'] = $role?->getId() ?? 0;
        }

        if ($withWitnesses) {
            uasort($tasks, fn($a, $b) => $a['priority'] < $b['priority']);

            $this->usedWitnesses[$date->__toString()] = [];
            foreach ($tasks as &$task) {

                $task['witnesses'] = $this->getWitnessesByRoleSorted($task['role'], $date);

                $task['suggested_witness'] = $this->getNexWitnessByRole($task['role'], $date)->current();
            }


            uasort($tasks, fn($a, $b) => $a['number'] > $b['number']);
        }

        return $tasks;
    }

    public function refreshTasks(int $year, int $week)
    {
        $this->tasksParser->refresh($year, $week);
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
            if (isset($this->usedWitnesses[(string)$date][$witness['witness_id']])) continue;
            $this->usedWitnesses[(string)$date][$witness['witness_id']] = $witness['witness_id'];
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
