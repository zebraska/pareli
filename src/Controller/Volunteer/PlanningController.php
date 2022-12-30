<?php

namespace App\Controller\Volunteer;

use App\Entity\Delivery;
use App\Entity\PlanningLine;
use App\Entity\PlanningWeek;
use App\Entity\Removal;
use App\Entity\Vehicle;
use App\Entity\Volunteer;
use App\Service\Ajax\AjaxResponse;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends AbstractController
{
    const CONTROLLER_NAME = 'Volunteer/PlanningController';

    #[Route('/volunteer/planning', name: 'app_volunteer_planning')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $today = new \DateTime();
        $year = $today->format("Y");
        $week = $today->format("W");
        $week_start = $today;
        $pWeek = $doctrine->getRepository(PlanningWeek::class)->findOneBy(['year' => $year, 'number' => $week]);
        if (is_null($pWeek)) {
            $pWeek = (new PlanningWeek())
                ->setYear($year)
                ->setnumber($week)
                ->setMondayDate($week_start->setISODate($today->format("Y"), $today->format("W")));
            $em = $doctrine->getManager();
            $em->persist($pWeek);
            $em->flush();
        }

        $linesPerDay[0] = ['title' => 'Lundi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 0])];
        $linesPerDay[1] = ['title' => 'Lundi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 1])];

        $linesPerDay[2] = ['title' => 'Mardi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 2])];
        $linesPerDay[3] = ['title' => 'Mardi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 3])];

        $linesPerDay[4] = ['title' => 'Mercredi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 4])];
        $linesPerDay[5] = ['title' => 'Mercredi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 5])];

        $linesPerDay[6] = ['title' => 'Jeudi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 6])];
        $linesPerDay[7] = ['title' => 'Jeudi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 7])];

        $linesPerDay[8] = ['title' => 'Vendredi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 8])];
        $linesPerDay[9] = ['title' => 'Vendredi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 9])];

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $ajaxResponse->addView(
                $this->render('volunteer/planning/content.html.twig', [
                    'pWeek' => $pWeek,
                    'linesPerDay' => $linesPerDay
                ])->getContent(),
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
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/planning/index.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'pWeek' => $pWeek,
            'linesPerDay' => $linesPerDay
        ]);
    }

    #[Route('/volunteer/planning/add/planning/line/{lineWeekId}/{day}', name: 'app_volunteer_planning_add_planning_line')]
    public function addPlanningLine(Request $request, ManagerRegistry $doctrine, int $lineWeekId, int $day): Response
    {
        $pLine = new PlanningLine();
        $pWeek = $doctrine->getRepository(PlanningWeek::class)->findOneBy(['id' => $lineWeekId]);
        if ($request->isXmlHttpRequest()) {
            $pLine->setPlanningWeek($pWeek);
            $pLine->setDay($day);
            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $em = $doctrine->getManager();
            $em->persist($pLine);
            $em->flush();

            $linesPerDay[0] = ['title' => 'Lundi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 0])];
            $linesPerDay[1] = ['title' => 'Lundi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 1])];

            $linesPerDay[2] = ['title' => 'Mardi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 2])];
            $linesPerDay[3] = ['title' => 'Mardi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 3])];

            $linesPerDay[4] = ['title' => 'Mercredi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 4])];
            $linesPerDay[5] = ['title' => 'Mercredi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 5])];

            $linesPerDay[6] = ['title' => 'Jeudi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 6])];
            $linesPerDay[7] = ['title' => 'Jeudi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 7])];

            $linesPerDay[8] = ['title' => 'Vendredi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 8])];
            $linesPerDay[9] = ['title' => 'Vendredi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 9])];

            $ajaxResponse->addView(
                $this->render('volunteer/planning/content.html.twig', [
                    'pWeek' => $pWeek,
                    'linesPerDay' => $linesPerDay
                ])->getContent(),
                'body-interface'
            );
            $this->addFlash('success', 'Ligne ajoutée');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/delete/planning/line/{pLineId}', name: 'app_volunteer_planning_delete_planning_line')]
    public function deletePlanningLine(Request $request, ManagerRegistry $doctrine, int $pLineId): Response
    {
        $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);

        $today = new \DateTime();
        $year = $today->format("Y");
        $week = $today->format("W");
        $week_start = $today;
        $pWeek = $doctrine->getRepository(PlanningWeek::class)->findOneBy(['year' => $year, 'number' => $week]);

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $em = $doctrine->getManager();
            $em->remove($pLine);
            $em->flush();

            $linesPerDay[0] = ['title' => 'Lundi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 0])];
            $linesPerDay[1] = ['title' => 'Lundi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 1])];

            $linesPerDay[2] = ['title' => 'Mardi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 2])];
            $linesPerDay[3] = ['title' => 'Mardi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 3])];

            $linesPerDay[4] = ['title' => 'Mercredi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 4])];
            $linesPerDay[5] = ['title' => 'Mercredi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 5])];

            $linesPerDay[6] = ['title' => 'Jeudi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 6])];
            $linesPerDay[7] = ['title' => 'Jeudi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 7])];

            $linesPerDay[8] = ['title' => 'Vendredi matin', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 8])];
            $linesPerDay[9] = ['title' => 'Vendredi après-midi', 'lines' => $doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $pWeek, 'day' => 9])];

            $ajaxResponse->addView(
                $this->render('volunteer/planning/content.html.twig', [
                    'pWeek' => $pWeek,
                    'linesPerDay' => $linesPerDay
                ])->getContent(),
                'body-interface'
            );
            $this->addFlash('success', 'Ligne supprimée');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/vehicle/selection/{pLineId}', name: 'app_volunteer_planning_get_vehicle_selection')]
    public function getVehicleSelection(Request $request, ManagerRegistry $doctrine, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $vehicles = $doctrine->getRepository(Vehicle::class)->findAll();
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/vehicles/vehicles.html.twig', [
                    'pLine' => $pLine,
                    'vehicles' => $vehicles,
                ])->getContent(),
                'modal-content'
            );
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/select/vehicle/{vehicleId}/{pLineId}', name: 'app_volunteer_planning_select_vehicle')]
    public function selectVehicle(Request $request, ManagerRegistry $doctrine, int $vehicleId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $vehicle = $doctrine->getRepository(Vehicle::class)->findOneBy(['id' => $vehicleId]);
            $lastVehicle = $pLine->getVehicle();
            if($lastVehicle){
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/selection/vehicles/cell.html.twig', [
                        'vehicle' => $lastVehicle,
                        'active' => false,
                    ])->getContent(),
                    'select-vehicle-'.$lastVehicle->getId()
                );
            }
            $pLine->setVehicle($vehicle);
            $em = $doctrine->getManager();
            $em->persist($pLine);
            $em->flush();

            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/vehicles/cell.html.twig', [
                    'vehicle' => $vehicle,
                    'active' => true,
                ])->getContent(),
                'select-vehicle-'.$vehicle->getId()
            );

            $ajaxResponse->addView(
                $vehicle->getName(),
                'selected-vehicle-'.$pLineId
            );

            $this->addFlash('success', 'Vehicule sélectionné');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/driver/selection/{pLineId}', name: 'app_volunteer_planning_get_driver_selection')]
    public function getDriverSelection(Request $request, ManagerRegistry $doctrine, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $drivers = $doctrine->getRepository(Volunteer::class)->findDriversForSelection();
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/drivers/drivers.html.twig', [
                    'pLine' => $pLine,
                    'drivers' => $drivers,
                ])->getContent(),
                'modal-content'
            );
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/select/driver/{driverId}/{pLineId}', name: 'app_volunteer_planning_select_driver')]
    public function selectDriver(Request $request, ManagerRegistry $doctrine, int $driverId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $driver = $doctrine->getRepository(Volunteer::class)->findOneBy(['id' => $driverId]);
            $lastDriver = $pLine->getDriver();
            if($lastDriver){
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/selection/drivers/cell.html.twig', [
                        'driver' => $lastDriver,
                        'active' => false,
                    ])->getContent(),
                    'select-driver-'.$lastDriver->getId()
                );
            }
            $pLine->setDriver($driver);
            $em = $doctrine->getManager();
            $em->persist($pLine);
            $em->flush();

            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/drivers/cell.html.twig', [
                    'driver' => $driver,
                    'active' => true,
                ])->getContent(),
                'select-driver-'.$driver->getId()
            );

            $ajaxResponse->addView(
                $driver->getDisplayName(),
                'selected-driver-'.$pLineId
            );

            $this->addFlash('success', 'Conducteur sélectionné');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/requests/selection/{pLineId}', name: 'app_volunteer_planning_get_requests_selection')]
    public function getRequestsSelection(Request $request, ManagerRegistry $doctrine, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $deliverys = $doctrine->getRepository(Delivery::class)->findBy(['state' => 0]);
            $removals = $doctrine->getRepository(Removal::class)->findBy(['state' => 0]);
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/requests/requests.html.twig', [
                    'pLine' => $pLine,
                    'deliverys' => $deliverys,
                    'removals' => $removals,
                ])->getContent(),
                'modal-content'
            );
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/add/removal/{removalId}/{pLineId}', name: 'app_volunteer_planning_add_removal')]
    public function addRemoval(Request $request, ManagerRegistry $doctrine, int $removalId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $removalId]);
            $removal->setPlanningLine($pLine);
            $removal->setState(1);
            $em = $doctrine->getManager();
            $em->persist($removal);
            $em->flush();
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);

            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/requests/removalCell.html.twig', [
                    'pLine' => $pLine,
                    'removal' => $removal,
                    'active' => true,
                ])->getContent(),
                'select-removal-'.$removal->getId()
            );

            $displayRequests='';
            foreach($pLine->getRemovals() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            foreach($pLine->getDeliverys() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-requests-'.$pLineId
            );

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/remove/removal/{removalId}/{pLineId}', name: 'app_volunteer_planning_remove_removal')]
    public function removeRemoval(Request $request, ManagerRegistry $doctrine, int $removalId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $removalId]);
            $removal->setPlanningLine(null);
            $removal->setState(0);
            $em = $doctrine->getManager();
            $em->persist($removal);
            $em->flush();
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);

            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/requests/removalCell.html.twig', [
                    'pLine' => $pLine,
                    'removal' => $removal,
                    'active' => false,
                ])->getContent(),
                'select-removal-'.$removal->getId()
            );

            $displayRequests='';
            foreach($pLine->getRemovals() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            foreach($pLine->getDeliverys() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-requests-'.$pLineId
            );
            
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/companions/selection/{pLineId}', name: 'app_volunteer_planning_get_companions_selection')]
    public function getCompanionsSelection(Request $request, ManagerRegistry $doctrine, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $companions = $doctrine->getRepository(Volunteer::class)->findAll();
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/companions/companions.html.twig', [
                    'pLine' => $pLine,
                    'companions' => $companions,
                ])->getContent(),
                'modal-content'
            );
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/add/companion/{companionId}/{pLineId}', name: 'app_volunteer_planning_add_companion')]
    public function addCompanion(Request $request, ManagerRegistry $doctrine, int $companionId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $companion = $doctrine->getRepository(Volunteer::class)->findOneBy(['id' => $companionId]);
            $pLine->addCompanion($companion);
            $em = $doctrine->getManager();
            $em->persist($pLine);
            $em->flush();
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);

            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/companions/cell.html.twig', [
                    'pLine' => $pLine,
                    'companion' => $companion,
                    'active' => true,
                ])->getContent(),
                'select-companion-'.$companion->getId()
            );

            $displayRequests='';
            foreach($pLine->getCompanions() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            foreach($pLine->getDeliverys() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-companions-'.$pLineId
            );

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/remove/companion/{companionId}/{pLineId}', name: 'app_volunteer_planning_remove_companion')]
    public function removeCompanion(Request $request, ManagerRegistry $doctrine, int $companionId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $companion = $doctrine->getRepository(Volunteer::class)->findOneBy(['id' => $companionId]);
            $pLine->removeCompanion($companion);
            $em = $doctrine->getManager();
            $em->persist($pLine);
            $em->flush();
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);

            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/companions/cell.html.twig', [
                    'pLine' => $pLine,
                    'companion' => $companion,
                    'active' => false,
                ])->getContent(),
                'select-companion-'.$companion->getId()
            );

            $displayRequests='';
            foreach($pLine->getCompanions() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            foreach($pLine->getDeliverys() as $request){
                $displayRequests = $displayRequests.$request->getDisplayName().'<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-companions-'.$pLineId
            );
            
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }
}