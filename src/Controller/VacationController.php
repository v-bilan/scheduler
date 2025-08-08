<?php

namespace App\Controller;

use App\Entity\Vacation;
use App\Entity\Witness;
use App\Form\VacationType;
use App\Repository\VacationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vacation')]
class VacationController extends AbstractController
{
    #[Route('/list/{id}', requirements: ['id' => '\d+'], defaults: ['id' => null], name: 'app_vacation_index', methods: ['GET'])]
    public function index(VacationRepository $vacationRepository, ?Witness $witness = null): Response
    {
        return $this->render('vacation/index.html.twig', [
            'witness' => $witness,
            'vacations' => $witness ? $witness->getVacations() : $vacationRepository->findAllWithWitnesses(),
        ]);
    }

    #[Route('/new/{id}', requirements: ['id' => '\d+'], defaults: ['id' => null], name: 'app_vacation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ?Witness $witness = null): Response
    {
        $vacation = new Vacation();
        $vacation->setWitness($witness);
        $form = $this->createForm(VacationType::class, $vacation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vacation);
            $entityManager->flush();

            return $this->redirectToRoute('app_vacation_index', ['id' => $witness->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vacation/new.html.twig', [
            'vacation' => $vacation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vacation_show', methods: ['GET'])]
    public function show(Vacation $vacation): Response
    {
        return $this->render('vacation/show.html.twig', [
            'vacation' => $vacation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vacation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vacation $vacation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VacationType::class, $vacation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('app_vacation_index', ['id' => $form->getData()?->getWitness()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vacation/edit.html.twig', [
            'vacation' => $vacation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vacation_delete', methods: ['POST'])]
    public function delete(Request $request, Vacation $vacation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $vacation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vacation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vacation_index', [], Response::HTTP_SEE_OTHER);
    }
}
