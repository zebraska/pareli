<?php

namespace App\Controller\Volunteer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends AbstractController
{
    #[Route('/volunteer/planning', name: 'app_volunteer_planning')]
    public function index(): Response
    {
        return $this->render('volunteer/planning/index.html.twig', [
            'controller_name' => 'PlanningController',
        ]);
    }
}
