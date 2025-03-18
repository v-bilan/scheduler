<?php

namespace App\Controller;

use App\Services\SynchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SyncController extends AbstractController
{
    public function __construct(private SynchManager $synchManager) {}
    #[Route('/sync', name: 'app_sync')]
    public function __invoke(): Response
    {
        $this->synchManager->tasks();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SyncController.php',
        ]);
    }
}
