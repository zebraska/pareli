<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Entity\Removal;
use DateInterval;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_volunteer');
    }
}
