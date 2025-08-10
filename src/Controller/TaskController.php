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
use App\Services\DateManager;
use App\Services\TaskManager;
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

    public function __construct(
        private DateManager $dateManager,
        private TaskManager $taskManager,
    ) {}

    #[Route('/task/{year}/{week}', name: 'app_task', methods: ['GET'])]
    public function index(int $year = 0, int $week = 0)
    {
        $date = $this->dateManager->getDate($year, $week);
        $year = $date->getFullYear();
        $week = $date->getWeek();
        $this->taskManager->refreshTasks($year, $week);
        //  dd($this->taskManager->getTasksData($date));
        return $this->render('task/index.html.twig', [
            'year' => $year,
            'week' => $week,
            'date' => $this->taskManager->getDateString($year, $week),
            'tasks' => $this->taskManager->getTasksData($date)
        ]);
    }

    #[Route('/task/{year}/{week}', name: 'app_taks_store', methods: ['POST'])]
    public function store(Request $request, int $year = 0, int $week = 0)
    {
        //TODO add validation
        $date = $this->dateManager->getDate($year, $week);
        $year = $date->getFullYear();
        $week = $date->getWeek();
        $witnesses = $request->get('witnesses');
        //dd($witnesses);

        $result = $this->taskManager->createSchedule($witnesses, $date);
        return $this->redirectToRoute('app_taks_store', ['year' => $year, 'week' => $week]);
    }
}
