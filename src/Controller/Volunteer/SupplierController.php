<?php

namespace App\Controller\Volunteer;

use App\Entity\Provider;
use App\Form\ProviderType;
use App\Service\Ajax\AjaxResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupplierController extends AbstractController
{
    #[Route('/volunteer/supplier', name: 'app_volunteer_supplier')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $providers = $doctrine->getRepository(Provider::class)->findAll();
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/supplier');

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/supplier/content.html.twig',
                    [
                        'providers' => $providers,
                    ]
                )->getContent(),
                'body-interface'
            );
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/supplier/index.html.twig', [
            'controller_name' => 'Volunteer/SupplierController',
            'providers' => $providers,
        ]);
    }

    #[Route('/volunteer/supplier/getform/create', name: 'app_volunteer_supplier_getform_create')]
    public function getformCreate(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $form = $this->createForm(ProviderType::class, new Provider(), [
                'action' => $this->generateUrl('app_volunteer_supplier_create'),
            ]);
            $ajaxResponse = new AjaxResponse('volunteer/supplier');

            $ajaxResponse->addView(
                $this->render('volunteer/supplier/modal/create.html.twig', ['form' => $form->createView()])->getContent(),
                'modal-content'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/supplier/index.html.twig', [
            'controller_name' => 'Volunteer/SupplierController',
        ]);
    }

    #[Route('/volunteer/supplier/create', name: 'app_volunteer_supplier_create')]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/supplier');
            $provider = new Provider();
            $form = $this->createForm(ProviderType::class, $provider, [
                'action' => $this->generateUrl('app_volunteer_supplier_create'),
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    $em->persist($provider);
                    $em->flush();
                    $ajaxResponse->addView(
                        $this->render('volunteer/supplier/content.html.twig')->getContent(),
                        'body-interface'
                    );
                    $this->addFlash('success', 'Fournisseur: ' . $provider->getName() . ' ajouté');
                } catch (\Exception $e) {
                    $ajaxResponse->setCloseModal(false);
                    $ajaxResponse->addView(
                        $this->render('volunteer/supplier/modal/create.html.twig', ['form' => $form->createView()])->getContent(),
                        'modal-content'
                    );
                    $this->addFlash('danger', 'Veuillez transmettre une capture d\'écran des données saisies dans le formulaire à l\'adresse cyril.contant@zebratero.com');
                }
            } else {
                $ajaxResponse->setCloseModal(false);
                $ajaxResponse->addView(
                    $this->render('volunteer/supplier/modal/create.html.twig', ['form' => $form->createView()])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/supplier/index.html.twig', [
            'controller_name' => 'Volunteer/SupplierController',
        ]);
    }
}
