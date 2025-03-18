<?php

namespace App\Controller;

use App\Entity\DateWithYearAndWeek;
use App\Entity\Role;
use App\Entity\TaskGroup;
use App\Entity\TaskWitnessDate;
use App\Entity\Witness;
use App\Repository\RoleRepository;
use App\Repository\TaskGroupRepository;
use App\Repository\TaskWitnessDateRepository;
use App\Repository\WitnessRepository;
use App\Services\TasksManager;
use App\Services\TasksParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TaskController extends AbstractController
{
    private array $cachedRoles = [];

    public function __construct(
        private RoleRepository $roleRepository,
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private TaskGroupRepository $taskGroupRepository,
        private WitnessRepository $witnessRepository,
        private TaskWitnessDateRepository $taskWitnessDateRepository,
    ) {}

    #[Route('/createScedule/{year}/{week}', name: 'app_create_scedule')]
    public function createScedule(
        TasksParser $tasksParser,
        Request $request,
        int $year,
        int $week
    ): Response {
        $date = $this->getDate($year, $week);
        $taskGroup = $this->taskGroupRepository->findOneBy(['date' => $date]);
        $tasks = $this->serializer->deserialize($taskGroup->getTasks(), '\App\Entity\Role[]', 'json');

        // dd($tasks);
        $sortedTasks = $this->getTaskByPriority($tasks);
        $suggestedWitness = [];
        $usedWitnesses = [];
        foreach ($sortedTasks as $key => $task) {
            $users = $this->witnessRepository->findByRoleId($task->getId(), $date);
            if (!$users) {
                throw  new \Exception('There is no witness for this role');
            }

            foreach ($users as $user) {
                if (!isset($usedWitnesses[$user->getId()])) {
                    $usedWitnesses[$user->getId()] = true;
                    $suggestedWitness[$key] = $user;
                    break;
                }
            }
        }

        $parsedTasks = $tasksParser->getTasks($date->getFullYear(), $date->getWeek());
        $tasksData = $this->getTasksDataFromTasks($parsedTasks['tasks']);

        $preparedScheduler = $this->getPreparedScheduler($date, $tasksData);

        $formBuilder = $this->createFormBuilder();

        foreach ($tasksData as $key => $task) {
            $formBuilder
                ->add($key, EntityType::class, [
                    'label' => $task['label'] .
                        (isset($preparedScheduler[$key]['witness']) ? ' (' . $preparedScheduler[$key]['witness'] . ')' : ''),
                    'class' => Witness::class,
                    'choice_label' => 'full_name',
                    'data' => $suggestedWitness[$key] ?? null,
                    'choices' => $this->witnessRepository->findByRoleName($tasks[$key]->getName()),
                    'attr' => [
                        'data-validation-of-duplication-target' => 'witness'
                    ],
                ]);
        }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach ($data as $taskLabel => $Witness) {
                $taskWitnessDate = $this->taskWitnessDateRepository->findOneBy([
                    'task' => $taskLabel,
                    'Date' => $date,
                ]);
                if (!$taskWitnessDate) {
                    $taskWitnessDate = new TaskWitnessDate();
                    $this->entityManager->persist($taskWitnessDate);
                }
                $taskWitnessDate->setTask($taskLabel);
                $taskWitnessDate->setDate($date);
                $Witness = $this->witnessRepository->find($Witness->getId());
                $taskWitnessDate->setWitness($Witness);
                $role = $this->roleRepository->find($tasks[$taskLabel]?->getId());
                $taskWitnessDate->setRole($role);
            }
            $this->entityManager->flush();
            return $this->redirectToRoute('app_show_task', [
                'year' => $date->getFullYear(),
                'week' => $date->getWeek()
            ]);
        }
        // dd($tasks);


        return $this->render('task/createScedule.html.twig', [
            'date' => $parsedTasks['date'],
            'form' => $form,
            'week' => $date->getWeek(),
            'year' => $date->getFullYear(),
            'preparedScheduler' => $preparedScheduler,
        ]);
    }

    private function getDate($year, $week): DateWithYearAndWeek
    {
        $date = new DateWithYearAndWeek();
        $year = $year ?: date_format($date, 'o');
        $week = $week ?: date_format($date, 'W');
        $date->setISODate($year, $week);
        $date->setTime(0, 0, 0);
        return $date;
    }

    private function getPreparedScheduler(\DateTime $date, array $tasksData): array
    {
        $preparedScheduler = [];
        $scheduler = $this->taskWitnessDateRepository->findBy(['date' => $date]);
        if ($scheduler && count($scheduler) == count($tasksData)) {
            $schedulerWithKey = [];
            foreach ($scheduler as $task) {
                $schedulerWithKey[$task->getTask()] = $task->getWitness()->getFullName();
            }
            foreach ($tasksData as $taskKey => $taskData) {
                $preparedScheduler[$taskKey] = [
                    'label' => $taskData["label"],
                    'witness' => $schedulerWithKey[$taskKey],
                ];
            }
        }
        return $preparedScheduler;
    }
    #[Route('/showTask/{year}/{week}', name: 'app_show_task')]
    public function showTask(
        TasksParser $tasksParser,
        int $year = 0,
        int $week = 0
    ): Response {
        $date = $this->getDate($year, $week);

        $parsedTasks = $tasksParser->getTasks($date->getFullYear(), $date->getWeek());
        $tasksData = $this->getTasksDataFromTasks($parsedTasks['tasks']);

        $preparedScheduler = $this->getPreparedScheduler($date, $tasksData);
        return $this->render('task/showScedule.html.twig', [
            'date' => $parsedTasks['date'],
            'preparedScheduler' => $preparedScheduler,
            'year' => $date->getFullYear(),
            'week' => $date->getWeek(),
        ]);
    }
    #[Route('/task/{year}/{week}', name: 'app_task')]
    public function index(
        TasksParser $tasksParser,
        Request $request,
        int $year = 0,
        int $week = 0
    ): Response {

        $date = $this->getDate($year, $week);

        $tasks = $tasksParser->getTasks($date->getFullYear(), $date->getWeek());
        $tasksData = $this->getTasksDataFromTasks($tasks['tasks']);

        $formBuilder = $this->createFormBuilder();
        foreach ($tasksData as $key => $task) {
            $formBuilder
                ->add($key, EntityType::class, [
                    'label' => $task['label'],
                    'class' => Role::class,
                    'choice_label' => 'name',
                    'data' => $this->findRoleByName($task['role'])
                ]);
        }
        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $stringTasks = $this->serializer->serialize($data, 'json');
            if (!($taskGroup = $this->taskGroupRepository->findOneBy(['date' => $date]))) {
                $taskGroup = new TaskGroup();
                $taskGroup->setDate($date);
            }

            $taskGroup->setTasks($stringTasks);
            if (!$taskGroup->getId()) {
                $this->entityManager->persist($taskGroup);
            }
            $this->entityManager->flush();
            return $this->redirectToRoute('app_create_scedule', [
                'year' => $date->getFullYear(),
                'week' => $date->getWeek()
            ]);
        }


        return $this->render('task/index.html.twig', [
            'date' => $tasks['date'],
            'form' => $form,
            'year' => $date->getFullYear(),
            'week' => $date->getWeek()
        ]);
    }

    private function findRoleByName($name = 'Призначений брат'): Role | null
    {
        if (!isset($this->cachedRoles[$name])) {
            $this->cachedRoles[$name] = $this->roleRepository->findOneBy(['name' => $name]);
        }
        return $this->cachedRoles[$name];
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
    private function getTaskByPriority(array $tasks)
    {
        uasort($tasks, function (Role $x, Role $y) {
            if ($x->getPriority() == $y->getPriority())
                return 0;
            else if ($x->getPriority() > $y->getPriority())
                return -1;
            else
                return 1;
        });
        return $tasks;
    }
}
