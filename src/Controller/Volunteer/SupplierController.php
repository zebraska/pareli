<?php

namespace App\Controller\Volunteer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupplierController extends AbstractController
{
    #[Route('/volunteer/supplier', name: 'app_volunteer_supplier')]
    public function index(): Response
    {
        return $this->render('volunteer/supplier/index.html.twig', [
            'controller_name' => 'SupplierController',
        ]);
    }
}
