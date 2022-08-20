<?php

namespace App\Controller\Volunteer;

use App\Service\Ajax\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RemovalsController extends AbstractController
{
    #[Route('/volunteer/removals', name: 'app_volunteer_removals')]
    public function index(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removals');

            $ajaxResponse->addView(
                $this->render('volunteer/removals/content.html.twig')->getContent(),
                'body-interface'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/removals/index.html.twig', [
            'controller_name' => 'Volunteer/RemovalsController',
        ]);
    }
}
