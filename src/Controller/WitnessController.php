<?php

namespace App\Controller;

use App\Controller\Traits\PagerFantaTrait;
use App\Entity\Witness;
use App\Form\WitnessType;
use App\Repository\WitnessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/witness')]
class WitnessController extends AbstractController
{
    use PagerFantaTrait;
    #[Route('/', name: 'app_witness_index', methods: ['GET'])]
    public function index(
        Request $request,
        WitnessRepository $witnessRepository
    ): Response {
        return $this->render('witness/index.html.twig', [
            'witnesses' => $this->getPagerFanta($request, $witnessRepository, 'fullName'),
        ]);
    }

    #[Route('/new', name: 'app_witness_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $witness = new Witness();
        $form = $this->createForm(WitnessType::class, $witness);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($witness);
            $entityManager->flush();

            return $this->redirectToRoute('app_witness_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('witness/new.html.twig', [
            'witness' => $witness,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_witness_show', methods: ['GET'])]
    public function show(Witness $witness): Response
    {
        return $this->render('witness/show.html.twig', [
            'witness' => $witness,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_witness_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Witness $witness, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WitnessType::class, $witness);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_witness_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('witness/edit.html.twig', [
            'witness' => $witness,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_witness_delete', methods: ['POST'])]
    public function delete(Request $request, Witness $witness, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$witness->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($witness);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_witness_index', [], Response::HTTP_SEE_OTHER);
    }
}
