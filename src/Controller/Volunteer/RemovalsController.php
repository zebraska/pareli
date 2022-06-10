<?php

namespace App\Controller\Volunteer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RemovalsController extends AbstractController
{
    #[Route('/volunteer/removals', name: 'app_volunteer_removals')]
    public function index(): Response
    {
        return $this->render('volunteer/removals/index.html.twig', [
            'controller_name' => 'RemovalsController',
        ]);
    }
}
