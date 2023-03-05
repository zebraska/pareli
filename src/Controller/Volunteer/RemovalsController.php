<?php

namespace App\Controller\Volunteer;

use App\Entity\Provider;
use App\Entity\Removal;
use App\Entity\PlanningWeek;
use App\Entity\RemovalContainerQuantity;
use App\Form\RemovalContainerQuantityType;
use App\Form\RemovalType;
use App\Service\Ajax\AjaxResponse;
use App\Service\Planning\Manager;
use App\Service\Spreadsheet\RemovalDeliveryModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class RemovalsController extends AbstractController
{
   
    const CONTROLLER_NAME = 'Volunteer/RemovalController';

    #[Route('/volunteer/removal', name: 'app_volunteer_removal')]
    public function index(Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $query = $doctrine->getRepository(Removal::class)->getPaginationMainQuery('', '','');

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        if (null !== $request->query->get('date1') && null !== $request->query->get('date2') && null !== $request->query->get('completeSwitch')){
            $dateStart = new \Datetime($request->query->get('date1'));
            $dateEnd = new \Datetime($request->query->get('date2'));                 
            $spreadsheetModel = new RemovalDeliveryModel($doctrine, $dateStart, $dateEnd);
            if($request->query->get('completeSwitch')) {
                $spreadsheetModel->completeSpreadsheet();
            }
            $spreadsheet = $spreadsheetModel->getSpreadsheet();                       
            $writer = new Xlsx($spreadsheet);
            $fileName = 'demandes_export.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);            
            return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
        }

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            $ajaxResponse->addView(
                $this->render(
                    'volunteer/removals/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => '',
                        'filterattach' => '',
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

        return $this->render('volunteer/removals/index.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'pagination' => $pagination,
            'search' => '',
            'filterattach' => '',
            'filter' => '',
            'page' => 1,
        ]);
    }

    #[Route('/volunteer/removal/getform/{id}', defaults: ["id" => null], name: 'app_volunteer_removal_getform')]
    public function getform(int $id = null, Request $request, ManagerRegistry $doctrine): Response
    {
        $provider=new Provider();   
        $providerId = $request->query->getInt('providerId', 0);

        if ($request->isXmlHttpRequest()) {
            $removal = new Removal();
            $removal->setDateRequest(new \DateTime());
            //if true show a return button instead of close
            $returnButton = $request->query->getBoolean('withReturn', false);
            if (!is_null($id)) {
                $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $id]);
            } 
            if ($providerId != 0) {
                $provider = $doctrine->getRepository(Provider::class)->findOneBy(['id' => $providerId]);
                $query = $doctrine->getRepository(Removal::class)->getLastRemovalByProvider($providerId,3);
                $lastRemovals = $query->getResult();
                $ajaxResponse = new AjaxResponse('volunteer/removal');
                $removal->setComment($provider->getComment());
            }
            $form = $this->createForm(RemovalType::class, $removal, [
                'action' => $this->generateUrl('app_volunteer_removal_create', ['id' => $id, 'providerId' => $providerId, 'returnButton' => $returnButton]),
            ]);
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            if (!is_null($id)) {
                $ajaxResponse->addView(
                $this->render('volunteer/removals/modal/formEdit.html.twig', ['form' => $form->createView(), 'provider' => $removal -> getProvider(), 'id' => $id, 'returnButton' => $returnButton])->getContent(),
                'modal-content');
            } else {
                $ajaxResponse->addView(
                $this->render('volunteer/removals/modal/formEdit.html.twig', ['form' => $form->createView(), 'provider' => $provider ,'id' => $id,  'lastRemovals' => $lastRemovals, 'returnButton' => $returnButton])->getContent(),
                'modal-content'
                );
            }
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->redirectToRoute("app_volunteer_removal");
           
    }
             
    

    #[Route('/volunteer/removal/search/{type}', name: 'app_volunteer_removal_search')]
    public function search(String $type, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $search = $request->query->get('search', '');
        $filterattach = $request->query->get('filterattach', '');
        $filter = $request->query->get('filter', '');
        $page = $request->query->getInt('page', 1);
        $query = $doctrine->getRepository(Removal::class)->getPaginationMainQuery($search, $filter,$filterattach);

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removals');
            $ajaxResponse->addView(
                $this->render(
                    'volunteer/removals/table/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filterattach' => $filterattach,
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
                        'volunteer/removals/component/filter.html.twig',
                        [
                            'search' => $search,
                            'filterattach' => $filterattach,
                            'filter' => $filter,
                            'page' => $page,
                            
                        ]
                    )->getContent(),
                    'select-filters'
                );
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/removals/component/filterattach.html.twig',
                        [
                            'search' => $search,
                            'filterattach' => $filterattach,
                            'filter' => $filter,
                            'page' => $page,
                            
                        ]
                    )->getContent(),
                    'select-filtersattach'
                );
            } else if ($type == 'filter') {
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/removals/component/search.html.twig',
                        [
                            'search' => $search,
                            'filterattach' => $filterattach,
                            'filter' => $filter,
                            'page' => $page,
                        ]
                    )->getContent(),
                    'input-search'
                );
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/removals/component/filterattach.html.twig',
                        [
                            'search' => $search,
                            'filterattach' => $filterattach,
                            'filter' => $filter,
                            'page' => $page,
                            
                        ]
                    )->getContent(),
                    'select-filtersattach'
                );
            }
            else if ($type == 'filterattach'){
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/removals/component/search.html.twig',
                        [
                            'search' => $search,
                            'filterattach' => $filterattach,
                            'filter' => $filter,
                            'page' => $page,
                        ]
                    )->getContent(),
                    'input-search'
                );
                $ajaxResponse->addView(
                    $this->render(
                        'volunteer/removals/component/filter.html.twig',
                        [
                            'search' => $search,
                            'filterattach' => $filterattach,
                            'filter' => $filter,
                            'page' => $page,
                            
                        ]
                    )->getContent(),
                    'select-filters'
                );
            }
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/removals/index.html.twig', [
            'controller_name' => 'Volunteer/removalsController',
            'pagination' => $pagination,
            'search' => '',
            'filterattach' => '',
            'filter' => '',
        ]);
    }




    #[Route('/volunteer/removal/create/{id}', defaults: ["id" => 0], name: 'app_volunteer_removal_create')]
    public function create(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $providerId = $request->query->getInt('providerId', 0);
        $returnButton = $request->query->getBoolean('returnButton', false);
        $removal = new Removal();
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            $removal = new Removal();
            $removal->setDateRequest(new \DateTime());
            if ($id != 0) {
                $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $id]);
            } else {
                $removal->setDateCreate(new \DateTime());
            }
            $form = $this->createForm(RemovalType::class, $removal, [
                'action' => $this->generateUrl('app_volunteer_removal_create', ['id' => $id]),
            ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    if ($providerId != 0) {
                        $provider = $doctrine->getRepository(Provider::class)->findOneBy(['id' => $providerId]);
                        $removal->setProvider($provider);
                        $removal->setState(0);
                       
                        foreach ($provider->getContainersQuantitys() as $pCQ) {
                            $rCQ = new RemovalContainerQuantity();
                            $rCQ->setContainer($pCQ->getContainer())
                                ->setQuantity($pCQ->getQuantity())
                                ->setRemoval($removal);
                            $em->persist($rCQ);
                            $removal->addRemovalContainerQuantity($rCQ);
                        } 
                        
                    }
                  
                    $em->persist($removal);
                    $em->flush();
                    $query = $doctrine->getRepository(Removal::class)->getPaginationMainQuery($removal->getProvider()->getName(), '','');
                  
                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        10 /*limit per page*/
                    );

                    if (!$returnButton){
                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/removals/content.html.twig',
                            [
                                'pagination' => $pagination,
                                'search' => $removal->getProvider()->getName(),
                                'filter' => '',
                                'filterattach' => '',
                                'page' => 1,
                            ]
                        )->getContent(),
                        'body-interface'
                    );
                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/menu/menu.html.twig',
                            [
                                'controller_name' => self::CONTROLLER_NAME,
                            ]
                        )->getContent(),
                        'menu-interface'
                    );
                    } else {
                        $nextRemoval = new Removal();
                        $nextRemoval->setDateRequest(new \DateTime());
                        $form = $this->createForm(RemovalType::class, $nextRemoval, [
                        'action' => $this->generateUrl('app_volunteer_removal_create', ['id' => null, 'providerId' => $removal->getProvider()->getId()]),
                        ]);                        
                        $query = $doctrine->getRepository(Removal::class)->getLastRemovalByProvider($removal->getProvider()->getId(),3);
                        $lastRemovals = $query->getResult();
                        $ajaxResponse->setCloseModal(false);
                        $ajaxResponse->addView(
                        $this->render('volunteer/removals/modal/formEdit.html.twig', ['form' => $form->createView(), 'provider' => $removal -> getProvider(), 'id' => null,  'lastRemovals' => $lastRemovals, 'returnButton' => false])->getContent(),
                        'modal-content');                    
                    }
                    $this->addFlash('success', 'Enlèvement pour le fournisseur: ' . $removal->getProvider()->getName() . ' [action réalisée]');
                } catch (\Exception $e) {
                    $ajaxResponse->setCloseModal(false);
                    $ajaxResponse->addView(
                        $this->render('volunteer/removals/modal/formEdit.html.twig', ['form' => $form->createView(), 'provider' => $removal->getProvider(), 'id' => $providerId])->getContent(),
                        'modal-content'
                    );
                    $this->addFlash('danger', 'Veuillez transmettre une capture d\'écran des données saisies dans le formulaire à l\'adresse cyril.contant@zebratero.com');
                }
            } else {
                $ajaxResponse->setCloseModal(false);
                $ajaxResponse->addView(
                    $this->render('volunteer/removals/modal/formEdit.html.twig', ['form' => $form->createView(), 'provider' => $removal->getProvider(), 'id' => $providerId])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute("app_volunteer_removal");
    }

    #[Route('/volunteer/removal/delete/{id}', name: 'app_volunteer_removal_delete')]
    public function delete(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator, int $id): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removal');

            $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $id]);
            $em = $doctrine->getManager();
            $em->remove($removal);
            $em->flush();

            $query = $doctrine->getRepository(Removal::class)->getPaginationMainQuery($removal->getProvider()->getName(), '','');

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/removals/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => '',
                        'filter' => '',
                        'filterattach' => '',
                        'page' => 1,
                    ]
                )->getContent(),
                'body-interface'
            );

            $this->addFlash('success', 'Demande supprimée');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute("app_volunteer_removal");
    }

    #[Route('/volunteer/removal/container/quantity/getform/{id}', defaults: ["id" => 0], name: 'app_volunteer_removal_container_quantity_getform')]
    public function containerQuantityGetform(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            $cQuantity = new RemovalContainerQuantity();

            $form = $this->createForm(RemovalContainerQuantityType::class, $cQuantity, [
                'action' => $this->generateUrl('app_volunteer_removal_container_quantity_create', ['id' => $id]),
            ]);

            $ajaxResponse = new AjaxResponse('volunteer/removal');

            $ajaxResponse->addView(
                $this->render('volunteer/provider/modal/containerQuantity.html.twig', ['form' => $form->createView()])->getContent(),
                'modal-content'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/removal/index.html.twig', [
            'controller_name' => 'Volunteer/RemovalsController',
        ]);
    }

    #[Route('/volunteer/removal/container/quantity/create/{id}', defaults: ["id" => 0], name: 'app_volunteer_removal_container_quantity_create')]
    public function containerQuantityCreate(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $id]);
            $cQuantity = new RemovalContainerQuantity();
            $form = $this->createForm(RemovalContainerQuantityType::class, $cQuantity, [
                'action' => $this->generateUrl('app_volunteer_removal_container_quantity_create', ['id' => $id]),
            ]);
            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                try {
                    $em = $doctrine->getManager();
                    $cQuantity->setRemoval($removal);
                    $em->persist($cQuantity);
                    $em->flush();

                    $query = $doctrine->getRepository(Removal::class)->getPaginationMainQuery($removal->getProvider()->getName(), '','');
                    $pagination = $paginator->paginate(
                        $query, /* query NOT result */
                        $request->query->getInt('page', 1), /*page number*/
                        10 /*limit per page*/
                    );
                    $ajaxResponse->addView(
                        $this->render(
                            'volunteer/removals/content.html.twig',
                            [
                                'pagination' => $pagination,
                                'search' => $removal->getProvider()->getName(),
                                'filter' => '',
                                'filterattach' => '',
                                'page' => 1,
                            ]
                        )->getContent(),
                        'body-interface'
                    );
                    $this->addFlash('success', 'Contenant ' . $cQuantity->getContainer()->getName() . ' [action réalisée]');
                } catch (\Exception $e) {
                    $ajaxResponse->setCloseModal(false);
                    $ajaxResponse->addView(
                        $this->render('volunteer/provider/modal/containerQuantity.html.twig', ['form' => $form->createView()])->getContent(),
                        'modal-content'
                    );
                    $this->addFlash('danger', 'Veuillez transmettre une capture d\'écran des données saisies dans le formulaire à l\'adresse cyril.contant@zebratero.com');
                }
            } else {
                $ajaxResponse->setCloseModal(false);
                $ajaxResponse->addView(
                    $this->render('volunteer/provider/modal/containerQuantity.html.twig', ['form' => $form->createView()])->getContent(),
                    'modal-content'
                );
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout');
            }
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/removal/index.html.twig', [
            'controller_name' => 'Volunteer/RemovalsController',
        ]);
    }

    #[Route('/volunteer/removal/container/quantity/delete/{id}', defaults: ["id" => 0], name: 'app_volunteer_removal_container_quantity_delete')]
    public function containerQuantityDelete(int $id = 0, Request $request, PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            $cQuantity = $doctrine->getRepository(RemovalContainerQuantity::class)->findOneBy(['id' => $id]);

            $em = $doctrine->getManager();
            $em->remove($cQuantity);
            $em->flush();

            $query = $doctrine->getRepository(Removal::class)->getPaginationMainQuery($cQuantity->getRemoval()->getProvider()->getName(), '','');

            $pagination = $paginator->paginate(
                $query, /* query NOT result */
                $request->query->getInt('page', 1), /*page number*/
                10 /*limit per page*/
            );

            $ajaxResponse->addView(
                $this->render(
                    'volunteer/removals/content.html.twig',
                    [
                        'pagination' => $pagination,
                        'search' => $cQuantity->getRemoval()->getProvider()->getName(),
                        'filter' => '',
                        'filterattach' => '',
                        'page' => 1,
                    ]
                )->getContent(),
                'body-interface'
            );
            $this->addFlash('success', 'Contenant: ' . $cQuantity->getContainer()->getName() . ' [supprimé]');

            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/removal/index.html.twig', [
            'controller_name' => 'Volunteer/RemovalsController',
        ]);
    }
    
    #[Route('/volunteer/removal/provider/view/{id}', name: 'app_volunteer_removal_provider_view')]
    public function viewRemovalByProvider(int $id, Request $request, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            
            $provider = $doctrine->getRepository(Provider::class)->findOneBy(['id' => $id]);
            $query = $doctrine->getRepository(Removal::class)->getLastRemovalByProvider($id,10);
            $lastRemovals = $query->getResult();
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            $ajaxResponse->addView(
                $this->render('volunteer/removals/modal/removalHistory.html.twig', ['provider' => $provider, 'lastRemovals' => $lastRemovals])->getContent(),
                'modal-content'
                );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
            
        }
        
        return $this->redirectToRoute("app_volunteer_removal");
        
    }
    
    #[Route('/volunteer/removal/spreadsheet/generate', name: 'app_volunteer_removal_spreadsheet_generate')]
    public function spreadsheetGenerate(Request $request, ManagerRegistry $doctrine): Response
    {
        if ($request->isXmlHttpRequest()) {
            
            $ajaxResponse = new AjaxResponse('volunteer/removal');
            $dateStart = new \DateTime();
            $dateEnd = new \DateTime();
            $dateEnd->modify('+1 week');
            $defaultData = ['dateStart' => $dateStart, 'dateEnd' => $dateEnd, 'completeSwitch' => false];
            $form = $this->createFormBuilder($defaultData, [
                'action' => $this->generateUrl('app_volunteer_removal_spreadsheet_generate'),
                'attr' => ['class' => 'form-check form-switch', 'style' => 'padding-left: 0']
            ])
                ->add('dateStart', \Symfony\Component\Form\Extension\Core\Type\DateType::class, ['label' => 'Début de la feuille', 'widget' => 'single_text'])
                ->add('dateEnd', \Symfony\Component\Form\Extension\Core\Type\DateType::class, ['label' => 'Fin de la feuille', 'widget' => 'single_text'])
                ->add('completeSwitch', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, ['label' => 'Informations complètes',
                                                                                                          'label_attr' => ['class' => 'form-check-label ms-2'],                                                                                                            
                                                                                                          'required' => false
                                                                                                         ])
                ->getForm();
            $ajaxResponse->addView(
                $this->render('volunteer/removals/modal/spreadsheet.html.twig', ['form' => $form->createView()])->getContent(),
                'modal-content'
                );
            
            $form->handleRequest($request);
            if ($form->isSubmitted() and $form->isValid()) {
                $dateStart = $form->getData()['dateStart'];
                $dateEnd = $form->getData()['dateEnd'];
                $completeSwitch = $form->getData()['completeSwitch'];
                $ajaxResponse->setRedirectTo($this->generateUrl('app_volunteer_removal', ['date1' => $dateStart->format('Y-m-d'), 'date2' => $dateEnd->format('Y-m-d'), 'completeSwitch' => $completeSwitch]));                
               
            }
            return $ajaxResponse->generateContent();
            
            
        }
        
        
        
        return $this->redirectToRoute("app_volunteer_removal");
    }
    
    #[Route('/volunteer/removal/spreadsheet/generate/redirect/{date1}/{date2}', name: 'app_volunteer_removal_spreadsheet_generate_redirect')]
    public function spreadsheetGenerateRedirect(Request $request, ManagerRegistry $doctrine): Response
    {
        return $this->redirectToRoute("app_volunteer_removal");
    }

}
