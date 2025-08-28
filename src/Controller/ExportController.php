<?php

namespace App\Controller;

use App\Form\ExportType;
use App\Repository\RoleRepository;
use App\Repository\TaskWitnessDateRepository;
use App\Services\DateManager;
use App\Services\TaskManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class ExportController extends AbstractController
{

    #[Route('/export', name: 'app_export_index')]
    public function index(
        Request $request,
        DateManager $dateManager,
        TaskManager $taskManager,
        RoleRepository $roleReposutory,
        TaskWitnessDateRepository $taskWitnessDateRepository
    ): Response {
        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $school = (bool) ($data['school'] ?? 0);

            $roles = $roleReposutory->getRolesIdBySchool($school);
            list($year, $week) = $dateManager->getMeetingDateData($dateFrom);
            $result = [];
            do {
                $date = $dateManager->getDate($year, $week);
                $year = $date->getFullYear();
                $week = $date->getWeek();
                $taskManager->refreshTasks($year, $week);

                $tasks = $taskManager->getTasksData($date);
                if ($tasks) {
                    $witnesses = [];
                    foreach ($tasks as $task) {
                        if (!isset($roles[$task['role_id']]) || !isset($task['witness'])) continue;
                        $witnesses[] = $task['witness']->getFullName();
                    }
                    $result[] = [$taskManager->getDateString($year, $week), ...$witnesses];
                }
                $week++;
            } while ($dateManager->getDate($year, $week) < $dateTo);


            $maxRows = max(array_map('count', $result));

            // транспонуємо
            $transposed = [];
            for ($i = 0; $i < $maxRows; $i++) {
                $row = [];
                foreach ($result as $col) {
                    $row[] = $col[$i] ?? "";
                }
                $transposed[] = $row;
            }

            $response = new StreamedResponse(function () use ($transposed) {
                $handle = fopen('php://output', 'w');

                // BOM для коректного відображення кирилиці в Excel
                fwrite($handle, "\xEF\xBB\xBF");

                foreach ($transposed as $row) {
                    fputcsv($handle, $row, ";");
                }

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="scheduller_data.csv"');

            return $response;
        }


        return $this->render('export/index.html.twig', [
            'controller_name' => 'ExportController',
            'form' => $form->createView(),
        ]);
    }
}
