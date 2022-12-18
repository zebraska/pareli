<?php

namespace App\Controller\Volunteer;

use App\Entity\Provider;
use App\Entity\Delivery;
use App\Entity\DeliveryContainerQuantity;
use App\Entity\Recycler;
use App\Form\DeliveryContainerQuantityType;
use App\Form\DeliveryType;
use App\Service\Ajax\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

class DeliveryController extends AbstractController
{
    const CONTROLLER_NAME = 'Volunteer/RemovalController';

    #[Route('/volunteer/delivery', name: 'app_volunteer_delivery')]
    public function index(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $query = $doctrine->getRepository(Delivery::class)->getPaginationMainQuery('', '');

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/delivery');
            $ajaxResponse->addView(
                $this->render(
                    'volunteer/delivery/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => '',
                        'filter' => '',
                        'page' => 1,
                    ]
                )->getContent(),
                'body-interface'
            );
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/delivery/index.html.twig', [
            'controller_name' => 'Volunteer/DeliveryController',
            'pagination' => $pagination,
            'search' => '',
            'filter' => '',
            'page' => 1,
        ]);
    }

    #[Route('/volunteer/delivery/getform/{id}', defaults: ["id" => null], name: 'app_volunteer_delivery_getform')]
    public function getform(int $id = null, Request $request, ManagerRegistry $doctrine): Response
    {
        $recyclerId = $request->query->getInt('recyclerId', 0);
        if ($request->isXmlHttpRequest()) {
            $delivery = new Delivery();
            if (!is_null($id)) {
                $delivery = $doctrine->getRepository(Delivery::class)->findOneBy(['id' => $id]);
            }
            if ($recyclerId != 0) {
                $recycler = $doctrine->getRepository(Recycler::class)->findOneBy(['id' => $recyclerId]);
            }
            $delivery->setDateRequest(new \DateTime());
            $form = $this->createForm(DeliveryType::class, $delivery, [
                'action' => $this->generateUrl('app_volunteer_delivery_create', ['id' => $id, 'recyclerId' => $recyclerId]),
            ]);
            $ajaxResponse = new AjaxResponse('volunteer/delivery');

            $ajaxResponse->addView(
                $this->render('volunteer/delivery/modal/all.html.twig', ['form' => $form->createView(), 'recycler' => $recycler])->getContent(),
                'modal-content'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->redirectToRoute("app_volunteer_delivery");
    }

    #[Route('/volunteer/delivery/create/{id}', defaults: ["id" => 0], name: 'app_volunteer_delivery_create')]
    public function create(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $recyclerId = $request->query->getInt('recyclerId', 0);
        $delivery = new Delivery();
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/delivery');
            $delivery = new Delivery();
            $delivery->setDateRequest(new \DateTime());
            if ($id != 0) {
                $delivery = $doctrine->getRepository(Delivery::class)->findOneBy(['id' => $id]);
            } else {
                $delivery->setDateCreate(new \DateTime());
            }
            $form = $this->createForm(DeliveryType::class, $delivery, [
                'action' => $this->generateUrl('app_volunteer_delivery_create', ['id' => $id]),
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    if ($recyclerId != 0) {
                        $recycler = $doctrine->getRepository(Recycler::class)->findOneBy(['id' => $recyclerId]);
                        $delivery->setRecycler($recycler);
                    }
                    $delivery->setState(0);
                    $em->persist($delivery);
                    $em->flush();
                    $query = $doctrine->getRepository(Delivery::class)->getPaginationMainQuery($delivery->getRecycler()->getName(), '');

                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        10 /*limit per page*/
                    );

                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/delivery/content.html.twig',
                            [
                                'pagination' => $pagination,
                                'search' => $delivery->getRecycler()->getName(),
                                'filter' => '',
                                'page' => 1,
                            ]
                        )->getContent(),
                        'body-interface'
                    );
                    //Update menu active
                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/menu/menu.html.twig',
                            [
                                'controller_name' => self::CONTROLLER_NAME,
                            ]
                        )->getContent(),
                        'menu-interface'
                    );
                    $this->addFlash('success', 'Enlèvement pour le fournisseur: ' . $delivery->getRecycler()->getName() . ' [action réalisée]');
                } catch (\Exception $e) {
                    $ajaxResponse->setCloseModal(false);
                    $ajaxResponse->addView(
                        $this->render('volunteer/delivery/modal/all.html.twig', ['form' => $form->createView(), 'recycler' => $delivery->getRecycler()])->getContent(),
                        'modal-content'
                    );
                    $this->addFlash('danger', 'Veuillez transmettre une capture d\'écran des données saisies dans le formulaire à l\'adresse cyril.contant@zebratero.com');
                }
            } else {
                $ajaxResponse->setCloseModal(false);
                $ajaxResponse->addView(
                    $this->render('volunteer/delivery/modal/all.html.twig', ['form' => $form->createView(), 'recycler' => $delivery->getRecycler()])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute("app_volunteer_delivery");
    }

    #[Route('/volunteer/delivery/delete/{id}', name: 'app_volunteer_delivery_delete')]
    public function delete(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator, int $id): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/delivery');

            $delivery = $doctrine->getRepository(Delivery::class)->findOneBy(['id' => $id]);
            $em = $doctrine->getManager();
            $em->remove($delivery);
            $em->flush();

            $query = $doctrine->getRepository(Delivery::class)->getPaginationMainQuery($delivery->getRecycler()->getName(), '');

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/delivery/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => '',
                        'filter' => '',
                        'page' => 1,
                    ]
                )->getContent(),
                'body-interface'
            );

            $this->addFlash('success', 'Demande supprimée');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute("app_volunteer_deliverys");
    }
}
