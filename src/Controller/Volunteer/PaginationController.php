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



class PaginationController extends AbstractController
{
    const CONTROLLER_NAME = 'Volunteer/ProviderController';

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
        
    
        $search = $request->query->get('search', '');
        $filter = $request->query->get('filter', '');
        $page = $request->query->getInt('page', 1);
        // $controller=$request->query->get('className','');
      
        if($page!=1){
            //on affiche la page précédente
            $page=$page-1;
        }
// $controller::class
        $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery($search, $filter);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $page, /*page number*/
            10 /*limit per page*/
        );


       $ajaxResponse = new AjaxResponse('volunteer/pagination');
               
        if ($request->isXmlHttpRequest()) {
            
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
            'controller_name' => 'Volunteer/providerController',
            'pagination' => $pagination,
            'search' => $search,
            'filter' => $filter,
            'page' => $page,
        ]);
    }

    #[Route('/volunteer/pagination/right', name: 'app_volunteer_pagination_right')]
    public function editpageright(Request $request,PaginatorInterface $paginator, ManagerRegistry $doctrine): Response
    {
        $search = $request->query->get('search', '');
        $filter = $request->query->get('filter', '');
        $page = $request->query->getInt('page', 1);

        $last = $request->query->getInt('last',1);
        if($page != $last){
            //on affiche la page suivante
            $page=$page+1;
           
        }

        $query = $doctrine->getRepository(Provider::class)->getPaginationMainQuery($search, $filter);
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $page, /*page number*/
            10 /*limit per page*/
        );

    $ajaxResponse = new AjaxResponse('volunteer/pagination');
        
     if ($request->isXmlHttpRequest()) {
         
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

}
