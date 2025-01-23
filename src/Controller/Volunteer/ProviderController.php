<?php

namespace App\Controller\Volunteer;

use App\Entity\ContainerQuantity;
use App\Entity\Provider;
use App\Form\ContainerQuantityType;
use App\Form\ProviderCommentType;
use App\Form\ProviderContactType;
use App\Form\ProviderInfoType;
use App\Form\ProviderType;
use App\Service\Ajax\AjaxResponse;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProviderController extends AbstractController
{
    const CONTROLLER_NAME = 'Volunteer/ProviderController';

    #[Route('/volunteer/provider', name: 'app_volunteer_provider')]
    public function index(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $filter='';
        if($this->getUser()->getUserIdentifier() == 'stnazaire'){
            $filter=2;
        }
        else if($this->getUser()->getUserIdentifier() == 'vertou'){
            $filter=1;
        }
        $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery('', $filter);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/provider');
            $ajaxResponse->addView(
                $this->render(
                    'volunteer/provider/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => '',
                        'filter' => $filter,
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

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'pagination' => $pagination,
            'search' => '',
            'filter' => $filter,
            'page' => 1,
        ]);
    }

    #[Route('/volunteer/provider/search/{type}', name: 'app_volunteer_provider_search')]
    public function search(String $type, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $search = $request->query->get('search', '');
        $filter = $request->query->get('filter', '');
        $page = $request->query->getInt('page', 1);
        $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery($search, $filter);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            50 /*limit per page*/
        );

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/provider');
            $ajaxResponse->addView(
                $this->render(
                    'volunteer/provider/table/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filter' => $filter,
                        'page' => $page,
                        
                    ]
                )->getContent(),
                'table-content'
            );
            if ($type == 'search') {
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/provider/component/filter.html.twig',
                        [
                            'search' => $search,
                            'filter' => $filter,
                            'page' => $page,
                        ]
                    )->getContent(),
                    'select-filters'
                );
            } else if ($type == 'filter') {
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/provider/component/search.html.twig',
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

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => 'Volunteer/providerController',
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'page' => $page,
        ]);
    }

    #[Route('/volunteer/provider/create/{type}/{id}', defaults: ["id" => 0], name: 'app_volunteer_provider_create')]
    public function create(String $type, int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $forms = [
            'info' => [
                ProviderInfoType::class,
                'volunteer/provider/modal/info.html.twig'
            ],
            'contact' => [
                ProviderContactType::class,
                'volunteer/provider/modal/contact.html.twig'
            ],
            'comment' => [
                ProviderCommentType::class,
                'volunteer/provider/modal/comment.html.twig'
            ],
        ];

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/provider');
            $provider = new Provider();
            if ($id != 0) {
                $provider = $doctrine->getRepository(Provider::class)->findOneBy(['id' => $id]);
            }
            $form = $this->createForm($forms[$type][0], $provider, [
                'action' => $this->generateUrl('app_volunteer_provider_create', ['type' => $type]),
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    $em->persist($provider);
                    $em->flush();

                    $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery($provider->getName(), '');

                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        50 /*limit per page*/
                    );

                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/provider/content.html.twig',
                            [
                                'pagination' => $pagination,
                                'search' => $provider->getName(),
                                'filter' => '',
                                'page' => 1,
                            ]
                        )->getContent(),
                        'body-interface'
                    );
                    $this->addFlash('success', 'Fournisseur: ' . $provider->getName() . ' [action réalisée]');
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
                    $this->render('volunteer/provider/modal/create.html.twig', ['form' => $form->createView()])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => 'Volunteer/providerController',
        ]);
    }

    #[Route('/volunteer/provider/getform/{type}/{id}', defaults: ["id" => 0], name: 'app_volunteer_provider_getform')]
    public function getform(string $type, int $id = 0, Request $request, ManagerRegistry $doctrine): Response
    {
        $forms = [
            'info' => [
                ProviderInfoType::class,
                'volunteer/provider/modal/info.html.twig'
            ],
            'contact' => [
                ProviderContactType::class,
                'volunteer/provider/modal/contact.html.twig'
            ],
            'comment' => [
                ProviderCommentType::class,
                'volunteer/provider/modal/comment.html.twig'
            ],
        ];

        if ($request->isXmlHttpRequest()) {
            $provider = new Provider();
            if (!is_null($id)) {
                $provider = $doctrine->getRepository(Provider::class)->findOneBy(['id' => $id]);
            }
            $form = $this->createForm($forms[$type][0], $provider, [
                'action' => $this->generateUrl('app_volunteer_provider_create', ['type' => $type, 'id' => $id]),
            ]);
            $ajaxResponse = new AjaxResponse('volunteer/provider');

            $ajaxResponse->addView(
                $this->render($forms[$type][1], ['form' => $form->createView(),'id' => $id])->getContent(),
                'modal-content'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => 'Volunteer/providerController',
        ]);
    }

    #[Route('/volunteer/provider/container/quantity/getform/{id}', defaults: ["id" => 0], name: 'app_volunteer_provider_container_quantity_getform')]
    public function containerQuantityGetform(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/provider');
            $cQuantity = new ContainerQuantity();

            $form = $this->createForm(ContainerQuantityType::class, $cQuantity, [
                'action' => $this->generateUrl('app_volunteer_provider_container_quantity_create', ['id' => $id]),
            ]);

            $ajaxResponse = new AjaxResponse('volunteer/provider');

            $ajaxResponse->addView(
                $this->render('volunteer/provider/modal/containerQuantity.html.twig', ['form' => $form->createView()])->getContent(),
                'modal-content'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => 'Volunteer/providerController',
        ]);
    }

    #[Route('/volunteer/provider/container/quantity/create/{id}', defaults: ["id" => 0], name: 'app_volunteer_provider_container_quantity_create')]
    public function containerQuantityCreate(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/provider');
            $provider = $doctrine->getRepository(Provider::class)->findOneBy(['id' => $id]);
            $cQuantity = new ContainerQuantity();
            $form = $this->createForm(ContainerQuantityType::class, $cQuantity, [
                'action' => $this->generateUrl('app_volunteer_provider_container_quantity_create', ['id' => $id]),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    $cQuantity->setProvider($provider);
                    $em->persist($cQuantity);
                    $em->flush();

                    $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery($provider->getName(), '');

                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        50 /*limit per page*/
                    );

                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/provider/content.html.twig',
                            [
                                'pagination' => $pagination,
                                'search' => $provider->getName(),
                                'filter' => '',
                                'page' => 1,
                            ]
                        )->getContent(),
                        'body-interface'
                    );
                    $this->addFlash('success', 'Fournisseur: ' . $provider->getName() . ' [action réalisée]');
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
                    $this->render('volunteer/provider/modal/create.html.twig', ['form' => $form->createView()])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => 'Volunteer/providerController',
        ]);
    }

    #[Route('/volunteer/provider/container/quantity/delete/{id}', defaults: ["id" => 0], name: 'app_volunteer_provider_container_quantity_delete')]
    public function containerQuantityDelete(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/provider');
            $cQuantity = $doctrine->getRepository(ContainerQuantity::class)->findOneBy(['id' => $id]);

            $em = $doctrine->getManager();
            $em->remove($cQuantity);
            $em->flush();

            $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery($cQuantity->getProvider()->getName(), '');

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                50 /*limit per page*/
            );

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/provider/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $cQuantity->getProvider()->getName(),
                        'filter' => '',
                        'page' => 1,
                    ]
                )->getContent(),
                'body-interface'
            );
            $this->addFlash('success', 'Fournisseur: ' . $cQuantity->getProvider()->getName() . ' [action réalisée]');

            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => 'Volunteer/providerController',
        ]);
    }

    #[Route('/volunteer/provider/delete/{id}', defaults: ["id" => 0], name: 'app_volunteer_provider_delete')]
    public function delete(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('search', '');
            $filter = $request->query->get('filter', '');
            $page = $request->query->getInt('page', 1);
            $ajaxResponse = new AjaxResponse('volunteer/provider');
            $provider = $doctrine->getRepository(Provider::class)->findOneBy(['id' => $id]);

            $em = $doctrine->getManager();
            $em->remove($provider);
            $em->flush();

            $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery($search, $filter);

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $page, /*page number*/
                50 /*limit per page*/
            );

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/provider/table/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filter' => $filter,
                        'page' => $page,
                    ]
                )->getContent(),
                'table-content'
            );
            $this->addFlash('success', 'Fournisseur: ' . $provider->getName() . ' [supprimé]');

            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/provider/index.html.twig', [
            'controller_name' => 'Volunteer/providerController',
        ]);
    }
}
