<?php

namespace App\Controller\Volunteer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Ajax\AjaxResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Provider;
use App\Entity\Recycler;
use App\Entity\Delivery;
use App\Entity\Removal;




class PaginationController extends AbstractController
{
    // const CONTROLLER_NAME = 'Volunteer/ProviderController';

    
    #[Route('/volunteer/pagination', name: 'app_volunteer_pagination')]
    public function index(): Response
    {
        return $this->render('pagination/index.html.twig', [
            'controller_name' => 'PaginationController',
        ]);
    }


    #[Route('/volunteer/pagination/left', name: 'app_volunteer_pagination_left')]
    public function editpageleft( Request $request,PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
       $tab = ['provider' => ['ClassName' => Provider::class, 'Content' => 'volunteer/provider/table/content.html.twig', 'Index' => 'volunteer/provider/index.html.twig'],
       'recycler' => ['ClassName' => Recycler::class, 'Content' => 'volunteer/recycler/table/content.html.twig', 'Index' => 'volunteer/recycler/index.html.twig'],
       'delivery' => ['ClassName' => Delivery::class, 'Content' => 'volunteer/delivery/table/content.html.twig', 'Index' => 'volunteer/delivery/index.html.twig'],
       'removals' => ['ClassName' => Removal::class, 'Content' => 'volunteer/removals/table/content.html.twig', 'Index' => 'volunteer/removals/index.html.twig']];
        $search = $request->query->get('search', '');
        $filter = $request->query->get('filter', '');
        $page = $request->query->getInt('page', 1);
        $className = $request->query->get('className','');
        if($className=='removals'){
            $filterattach = $request->query->get('filterattach', '');
        }
      
        if($page!=1){
            //on affiche la page précédente
            $page=$page-1;
        }

        $query = $doctrine->getRepository($tab[$className]['ClassName'])->getPaginationMainQuery($search, $filter);
        
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $page, /*page number*/
            10 /*limit per page*/
        );


       $ajaxResponse = new AjaxResponse('volunteer/pagination');
       if($className=='removals'){       
            if ($request->isXmlHttpRequest()) {
            
            $ajaxResponse->addView(
                $this->render(
                   
                    $tab[$className]['Content'],
                    [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filter' => $filter,
                        'filterattach' => $filterattach,
                        'page' => $page,
                        
                    ]
                )->getContent(),
                'table-content'
            );
             //Update menu active
            $ajaxResponse->addView(
                $this->render(
                 'volunteer/menu/menu.html.twig',
                 [
                    'controller_name' => 'Volunteer'.$className.'ProviderController',
                 ]
                )->getContent(),
                'menu-interface'
             );

            
            return $ajaxResponse->generateContent();
         }
         
         return $this->render( $tab[$className]['Index'], [
            'controller_name' => 'Volunteer/'.$className.'providerController',
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'filterattach' => $filterattach,
            'page' => $page,
        ]);
    }else{
        if ($request->isXmlHttpRequest()) {
            
            $ajaxResponse->addView(
                $this->render(
                   
                    $tab[$className]['Content'],
                    [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filter' => $filter,
                        'page' => $page,
                        
                    ]
                )->getContent(),
                'table-content'
            );
             //Update menu active
            $ajaxResponse->addView(
                $this->render(
                 'volunteer/menu/menu.html.twig',
                 [
                    'controller_name' => 'Volunteer'.$className.'ProviderController',
                 ]
                )->getContent(),
                'menu-interface'
             );

            
            return $ajaxResponse->generateContent();
         }
         
         return $this->render( $tab[$className]['Index'], [
            'controller_name' => 'Volunteer/'.$className.'providerController',
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'page' => $page,
        ]);
    }
}

    #[Route('/volunteer/pagination/right', name: 'app_volunteer_pagination_right')]
    public function editpageright(Request $request,PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $tab = ['provider' => ['ClassName' => Provider::class, 'Content' => 'volunteer/provider/table/content.html.twig', 'Index' => 'volunteer/provider/index.html.twig'],
        'recycler' => ['ClassName' => Recycler::class, 'Content' => 'volunteer/recycler/table/content.html.twig', 'Index' => 'volunteer/recycler/index.html.twig'],
        'delivery' => ['ClassName' => Delivery::class, 'Content' => 'volunteer/delivery/table/content.html.twig', 'Index' => 'volunteer/delivery/index.html.twig'],
        'removals' => ['ClassName' => Removal::class, 'Content' => 'volunteer/removals/table/content.html.twig', 'Index' => 'volunteer/removals/index.html.twig']];
        $search = $request->query->get('search', '');
        $filter = $request->query->get('filter', '');
        $page = $request->query->getInt('page', 1);
        $className = $request->query->get('className','');
        if($className=='removals'){
            $filterattach = $request->query->get('filterattach', '');
        }
        $last = $request->query->getInt('last',1);
        if($page != $last){
            //on affiche la page suivante
            $page=$page+1;
           
        }
        
        $query = $doctrine->getRepository($tab[$className]['ClassName'])->getPaginationMainQuery($search, $filter);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $page, /*page number*/
            10 /*limit per page*/
        );

        $ajaxResponse = new AjaxResponse('volunteer/pagination');
        if($className=='removals'){
            if ($request->isXmlHttpRequest()) {
             
                 $ajaxResponse->addView(
                    $this->render(
                        $tab[$className]['Template'],
                     [
                        'pagination' => $pagination,
                        'search' => $search,
                        'filter' => $filter,
                        'filterattach' => $filterattach,
                        'page' => $page,
                        
                    ]
                     )->getContent(),
                    'table-content'
                );
   
                //Update menu active
                 $ajaxResponse->addView(
                    $this->render(
                        'volunteer/menu/menu.html.twig',
                    [
                         'controller_name' => 'Volunteer/'.$className.'Controller',
                     ]
                    )->getContent(),
                    'menu-interface'
                );
                return $ajaxResponse->generateContent();
            }
                return $this->render($tab[$className]['Index'], [
                 'controller_name' => 'Volunteer/'.$className.'Controller',
                 'pagination' => $pagination,
                 'search' => $search,
                 'filter' => $filter,
                 'filterattach' => $filterattach,
                 'page' => $page,
                 ]);
       
         }
        else{ 
                if ($request->isXmlHttpRequest()) {
                    $ajaxResponse->addView(
                    $this->render(
                
                         $tab[$className]['Template'],
                     [
                         'pagination' => $pagination,
                         'search' => $search,
                         'filter' => $filter,
                         'page' => $page,
                        
                     ]
                     )->getContent(),
                    'table-content'
                     );

                    //Update menu active
                    $ajaxResponse->addView(
                    $this->render(
                     'volunteer/menu/menu.html.twig',
                     [
                         'controller_name' => 'Volunteer/'.$className.'Controller',
                     ]
                     )->getContent(),
                     'menu-interface'
                     );

                    return $ajaxResponse->generateContent();
                }
                 return $this->render($tab[$className]['Index'], [
                    'controller_name' => 'Volunteer/'.$className.'Controller',
                     'pagination' => $pagination,
                    'search' => $search,
                    'filter' => $filter,
                    'page' => $page,
                    ]);
             }
     }

}
