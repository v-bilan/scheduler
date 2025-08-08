<?php

namespace App\Controller;

use App\Form\ExportType;
use App\Repository\TaskWitnessDateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ExportController extends AbstractController
{
    #[Route('/export', name: 'app_export')]
    public function index(Request $request, TaskWitnessDateRepository $taskWitnessDateRepository): Response
    {
        $form = $this->createForm(ExportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dateFrom = $data['dateFrom'];
            $dateTo = $data['dateTo'];
            $items = $taskWitnessDateRepository->findByRange($dateFrom, $dateTo);
            dd($items);
            // ... логіка фільтрації або обробки дат
        }


        return $this->render('export/index.html.twig', [
            'controller_name' => 'ExportController',
            'form' => $form->createView(),
        ]);
    }
}
