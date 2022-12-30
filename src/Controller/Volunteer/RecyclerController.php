<?php

namespace App\Controller\Volunteer;

use App\Entity\ContainerQuantity;
use App\Entity\Recycler;
use App\Form\ContainerQuantityType;
use App\Form\RecyclerCommentType;
use App\Form\RecyclerContactType;
use App\Form\RecyclerInfoType;
use App\Form\RecyclerType;
use App\Service\Ajax\AjaxResponse;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecyclerController extends AbstractController
{
    const CONTROLLER_NAME = 'Volunteer/RecyclerController';

    #[Route('/volunteer/recycler', name: 'app_volunteer_recycler')]
    public function index(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $query = $doctrine->getRepository(Recycler::class)->getPaginationMainQuery('', '');

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/recycler');
            $ajaxResponse->addView(
                $this->render(
                    'volunteer/recycler/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => '',
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
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'pagination' => $pagination,
            'search' => '',
            'filter' => '',
            'page' => 1,
        ]);
    }

    #[Route('/volunteer/recycler/search/{type}', name: 'app_volunteer_recycler_search')]
    public function search(String $type, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $search = $request->query->get('search', '');
        $filter = $request->query->get('filter', '');
        $page = $request->query->getInt('page', 1);
        $query = $doctrine->getRepository(Recycler::class)->getPaginationMainQuery($search, $filter);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/recycler');
            $ajaxResponse->addView(
                $this->render(
                    'volunteer/recycler/table/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filter' => $filter,
                        'page' => $page,
                        'page' => $page,
                    ]
                )->getContent(),
                'table-content'
            );
            if ($type == 'search') {
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/recycler/component/filter.html.twig',
                        [
                            'search' => $search,
                            'filter' => $filter,
                            'page' => $page,
                        ]
                    )->getContent(),
                    'select-filters'
                );
            } else if ($type == 'search') {
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/recycler/component/search.html.twig',
                        [
                            'search' => $search,
                            'filter' => $filter,
                            'page' => $page,
                        ]
                    )->getContent(),
                    'input-search'
                );
            }
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => 'Volunteer/recyclerController',
            'pagination' => $pagination,
            'search' => '',
            'filter' => '',
        ]);
    }

    #[Route('/volunteer/recycler/getform/{id}', defaults: ["id" => null], name: 'app_volunteer_recycler_getform')]
    public function getformCreate(int $id = null, Request $request, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $recycler = new Recycler();
            if (!is_null($id)) {
                $recycler = $doctrine->getRepository(Recycler::class)->findOneBy(['id' => $id]);
            }
            $form = $this->createForm(RecyclerType::class, $recycler, [
                'action' => $this->generateUrl('app_volunteer_recycler_create', ['id' => $id]),
            ]);
            $ajaxResponse = new AjaxResponse('volunteer/recycler');

            $ajaxResponse->addView(
                $this->render('volunteer/recycler/modal/all.html.twig', ['form' => $form->createView(),'id' => $id])->getContent(),
                'modal-content'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => 'Volunteer/recyclerController',
        ]);
    }

    #[Route('/volunteer/recycler/create/{id}', defaults: ["id" => 0], name: 'app_volunteer_recycler_create')]
    public function create(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/recycler');
            $recycler = new Recycler();
            if ($id != 0) {
                $recycler = $doctrine->getRepository(Recycler::class)->findOneBy(['id' => $id]);
            }
            $form = $this->createForm(RecyclerType::class, $recycler, [
                'action' => $this->generateUrl('app_volunteer_recycler_create'),
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    $em->persist($recycler);
                    $em->flush();

                    $query = $doctrine->getRepository(Recycler::class)->getPaginationMainQuery($recycler->getName(), '');

                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        10 /*limit per page*/
                    );

                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/recycler/content.html.twig',
                            [
                                'pagination' => $pagination,
                                'search' => $recycler->getName(),
                                'filter' => '',
                                'page' => 1,
                            ]
                        )->getContent(),
                        'body-interface'
                    );
                    $this->addFlash('success', 'Recycleur: ' . $recycler->getName() . ' [action réalisée]');
                } catch (\Exception $e) {
                    $ajaxResponse->setCloseModal(false);
                    $ajaxResponse->addView(
                        $this->render('volunteer/recycler/modal/all.html.twig', ['form' => $form->createView()])->getContent(),
                        'modal-content'
                    );
                    $this->addFlash('danger', 'Veuillez transmettre une capture d\'écran des données saisies dans le formulaire à l\'adresse cyril.contant@zebratero.com');
                }
            } else {
                $ajaxResponse->setCloseModal(false);
                $ajaxResponse->addView(
                    $this->render('volunteer/recycler/modal/create.html.twig', ['form' => $form->createView()])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => 'Volunteer/recyclerController',
        ]);
    }

    #[Route('/volunteer/recycler/container/quantity/getform/{id}', defaults: ["id" => 0], name: 'app_volunteer_recycler_container_quantity_getform')]
    public function containerQuantityGetform(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/recycler');
            $cQuantity = new ContainerQuantity();

            $form = $this->createForm(ContainerQuantityType::class, $cQuantity, [
                'action' => $this->generateUrl('app_volunteer_recycler_container_quantity_create', ['id' => $id]),
            ]);

            $ajaxResponse = new AjaxResponse('volunteer/recycler');

            $ajaxResponse->addView(
                $this->render('volunteer/recycler/modal/containerQuantity.html.twig', ['form' => $form->createView()])->getContent(),
                'modal-content'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => 'Volunteer/recyclerController',
        ]);
    }

    #[Route('/volunteer/recycler/container/quantity/create/{id}', defaults: ["id" => 0], name: 'app_volunteer_recycler_container_quantity_create')]
    public function containerQuantityCreate(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/recycler');
            $recycler = $doctrine->getRepository(Recycler::class)->findOneBy(['id' => $id]);
            $cQuantity = new ContainerQuantity();
            $form = $this->createForm(ContainerQuantityType::class, $cQuantity, [
                'action' => $this->generateUrl('app_volunteer_recycler_container_quantity_create', ['id' => $id]),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    $cQuantity->setRecycler($recycler);
                    $em->persist($cQuantity);
                    $em->flush();

                    $query = $doctrine->getRepository(Recycler::class)->getPaginationMainQuery($recycler->getName(), '');

                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        10 /*limit per page*/
                    );

                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/recycler/content.html.twig',
                            [
                                'pagination' => $pagination,
                                'search' => $recycler->getName(),
                                'filter' => '',
                                'page' => 1,
                            ]
                        )->getContent(),
                        'body-interface'
                    );
                    $this->addFlash('success', 'Recycleur: ' . $recycler->getName() . ' [action réalisée]');
                } catch (\Exception $e) {
                    $ajaxResponse->setCloseModal(false);
                    $ajaxResponse->addView(
                        $this->render($forms[$type][1], ['form' => $form->createView()])->getContent(),
                        'modal-content'
                    );
                    $this->addFlash('danger', 'Veuillez transmettre une capture d\'écran des données saisies dans le formulaire à l\'adresse cyril.contant@zebratero.com');
                }
            } else {
                $ajaxResponse->setCloseModal(false);
                $ajaxResponse->addView(
                    $this->render('volunteer/recycler/modal/create.html.twig', ['form' => $form->createView()])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => 'Volunteer/recyclerController',
        ]);
    }

    #[Route('/volunteer/recycler/container/quantity/delete/{id}', defaults: ["id" => 0], name: 'app_volunteer_recycler_container_quantity_delete')]
    public function containerQuantityDelete(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/recycler');
            $cQuantity = $doctrine->getRepository(ContainerQuantity::class)->findOneBy(['id' => $id]);

            $em = $doctrine->getManager();
            $em->remove($cQuantity);
            $em->flush();

            $query = $doctrine->getRepository(Recycler::class)->getPaginationMainQuery($cQuantity->getRecycler()->getName(), '');

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/recycler/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $cQuantity->getRecycler()->getName(),
                        'filter' => '',
                        'page' => 1,
                    ]
                )->getContent(),
                'body-interface'
            );
            $this->addFlash('success', 'Recycleur: ' . $cQuantity->getRecycler()->getName() . ' [action réalisée]');

            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => 'Volunteer/recyclerController',
        ]);
    }

    #[Route('/volunteer/recycler/delete/{id}', defaults: ["id" => 0], name: 'app_volunteer_recycler_delete')]
    public function delete(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search', '');
            $filter = $request->query->get('filter', '');
            $page = $request->query->getInt('page', 1);
            $ajaxResponse = new AjaxResponse('volunteer/recycler');
            $recycler = $doctrine->getRepository(Recycler::class)->findOneBy(['id' => $id]);

            $em = $doctrine->getManager();
            $em->remove($recycler);
            $em->flush();

            $query = $doctrine->getRepository(Recycler::class)->getPaginationMainQuery($search, $filter);

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $page, /*page number*/
                10 /*limit per page*/
            );

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/recycler/table/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filter' => $filter,
                        'page' => $page,
                    ]
                )->getContent(),
                'table-content'
            );
            $this->addFlash('success', 'Recycleur: ' . $recycler->getName() . ' [supprimé]');

            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/recycler/index.html.twig', [
            'controller_name' => 'Volunteer/recyclerController',
        ]);
    }
}
