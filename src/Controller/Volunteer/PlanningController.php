<?php

namespace App\Controller\Volunteer;

use App\Service\Ajax\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends AbstractController
{
    #[Route('/volunteer/planning', name: 'app_volunteer_planning')]
    public function index(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $ajaxResponse->addView(
                $this->render('volunteer/planning/content.html.twig')->getContent(),
                'body-interface'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/planning/index.html.twig', [
            'controller_name' => 'Volunteer/PlanningController',
        ]);
    }
}
