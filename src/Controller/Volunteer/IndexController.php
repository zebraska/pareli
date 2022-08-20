<?php

namespace App\Controller\Volunteer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/volunteer', name: 'app_volunteer')]
    public function index(): Response
    {
        return $this->render('volunteer/index.html.twig', [
            'controller_name' => 'Volunteer/IndexController',
        ]);
    }
}
