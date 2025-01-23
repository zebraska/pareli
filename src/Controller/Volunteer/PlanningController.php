<?php

namespace App\Controller\Volunteer;

use App\Entity\Delivery;
use App\Entity\PlanningLine;
use App\Entity\PlanningWeek;
use App\Entity\Removal;
use App\Entity\Vehicle;
use App\Entity\Volunteer;
use App\Service\Ajax\AjaxResponse;
use App\Service\Planning\Manager;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Dompdf\Dompdf;
use Dompdf\Options;

class PlanningController extends AbstractController
{
    const CONTROLLER_NAME = 'Volunteer/PlanningController';

    #[Route('/volunteer/planning', name: 'app_volunteer_planning')]
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $filter = 0;
        if ($this->getUser()->getUserIdentifier() == 'stnazaire') {
            $filter = 1;
        }
        else if ($this->getUser()->getUserIdentifier() == 'admin') {
            $filter = 2;
        }
        //Parses a time string according to a specified format
        if (isset($_GET['m']) && \DateTime::createFromFormat('Y-m-d', $_GET['m'])) {
            $today = \DateTime::createFromFormat('Y-m-d', $_GET['m']);
        } else {
            $today = new \DateTime();
        }
        $year = $today->format("Y");
        $week = $today->format("W");
        $week_start = $today;
		$dayName = $today->format('l');
		if(($today->format("d") == 30 || $today->format("d") == 31 ) && $today->format("m")==12 && ($dayName === 'Monday' || $dayName === 'Tuesday')){
			$year = $year+1;
		}
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

        $planningManager = new Manager($doctrine, $pWeek, $filter);

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $ajaxResponse->addView(
                $this->render('volunteer/planning/content.html.twig', [
                    'pWeek' => $pWeek,
                    'linesPerDay' => $planningManager->getLinesPerDay(),
                    'filter' => $filter
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
            'linesPerDay' => $planningManager->getLinesPerDay(),
            'filter' => $filter
        ]);
    }

    #[Route('/volunteer/planning/filter/{filter}/{pWeekId}', name: 'app_volunteer_planning_filter')]
    public function filter(Request $request, ManagerRegistry $doctrine, int $filter, int $pWeekId): Response
    {
        $pWeek = $doctrine->getRepository(PlanningWeek::class)->findOneBy(['id' => $pWeekId]);
        $planningManager = new Manager($doctrine, $pWeek, $filter);

        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $ajaxResponse->addView(
                $this->render('volunteer/planning/content.html.twig', [
                    'pWeek' => $pWeek,
                    'linesPerDay' => $planningManager->getLinesPerDay(),
                    'filter' => $filter
                ])->getContent(),
                'body-interface'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }

        return $this->render('volunteer/planning/index.html.twig', [
            'controller_name' => self::CONTROLLER_NAME,
            'pWeek' => $pWeek,
            'linesPerDay' => $planningManager->getLinesPerDay(),
            'filter' => $filter
        ]);
    }

    #[Route('/volunteer/planning/add/planning/line/{lineWeekId}/{day}/{filter}', name: 'app_volunteer_planning_add_planning_line')]
    public function addPlanningLine(Request $request, ManagerRegistry $doctrine, int $lineWeekId, int $day, int $filter): Response
    {
        $pLine = new PlanningLine();
        $pWeek = $doctrine->getRepository(PlanningWeek::class)->findOneBy(['id' => $lineWeekId]);
        if ($request->isXmlHttpRequest()) {
            $pLine->setPlanningWeek($pWeek);
            $pLine->setDay($day);
            $pLine->setValid(false);
            if ($filter == 0) {
                $pLine->setAttachment("Vertou");
            } else if ($filter == 1) {
                $pLine->setAttachment("Saint-Nazaire");
            } else {
                $pLine->setAttachment("Tous");
            }

            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $em = $doctrine->getManager();
            $em->persist($pLine);
            $em->flush();

            $planningManager = new Manager($doctrine, $pWeek, $filter);

            $ajaxResponse->addView(
                $this->render('volunteer/planning/dayLines.html.twig', [
                    'pWeek' => $pWeek,
                    'linePerDay' => $planningManager->getLinesPerDay()[$day],
                    'filter' => $filter
                ])->getContent(),
                'dayLines' . $day
            );
            $this->addFlash('success', 'Ligne ajoutée');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/delete/planning/line/{lineWeekId}/{pLineId}/{filter}', name: 'app_volunteer_planning_delete_planning_line')]
    public function deletePlanningLine(Request $request, ManagerRegistry $doctrine, int $lineWeekId, int $pLineId, int $filter): Response
    {
        $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);

        $today = new \DateTime();
        $year = $today->format("Y");
        $week = $today->format("W");
        $week_start = $today;
        $pWeek = $doctrine->getRepository(PlanningWeek::class)->findOneBy(['id' => $lineWeekId]);
        $day = $pLine->getDay();
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $em = $doctrine->getManager();
            $em->remove($pLine);
            $em->flush();

            $planningManager = new Manager($doctrine, $pWeek, $filter);

            $ajaxResponse->addView(
                $this->render('volunteer/planning/dayLines.html.twig', [
                    'pWeek' => $pWeek,
                    'linePerDay' => $planningManager->getLinesPerDay()[$day],
                    'filter' => $filter
                ])->getContent(),
                'dayLines' . $day
            );
            $this->addFlash('success', 'Ligne supprimée');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }

        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/vehicle/selection/{pLineId}/{filter}', name: 'app_volunteer_planning_get_vehicle_selection')]
    public function getVehicleSelection(Request $request, ManagerRegistry $doctrine, int $pLineId, int $filter): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $attachment = $pLine->getAttachment();
            if ($attachment == "Tous") {
                $vehicles = $doctrine->getRepository(Vehicle::class)->findBy(['enable' => true]);
            } else {
                $vehicles = $doctrine->getRepository(Vehicle::class)->findBy(['attachment' => $attachment, 'enable' => true]);
            }

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
            if ($lastVehicle) {
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/selection/vehicles/cell.html.twig', [
                        'vehicle' => $lastVehicle,
                        'active' => false,
                    ])->getContent(),
                    'select-vehicle-' . $lastVehicle->getId()
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
                'select-vehicle-' . $vehicle->getId()
            );

            $ajaxResponse->addView(
                $vehicle->getDisplayName(),
                'selected-vehicle-' . $pLineId
            );

            $this->addFlash('success', 'Vehicule sélectionné');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/driver/selection/{pLineId}/{filter}', name: 'app_volunteer_planning_get_driver_selection')]
    public function getDriverSelection(Request $request, ManagerRegistry $doctrine, int $pLineId, int $filter): Response
    {

        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $attachment = $pLine->getAttachment();
            if ($pLine->getVehicle()?->getHgv()) {
                if ($attachment == "Tous") {
                    $drivers = $doctrine->getRepository(Volunteer::class)->findHgvDriversForSelection();
                } else {
                    $drivers = $doctrine->getRepository(Volunteer::class)->findHgvDriversForSelection($attachment);
                }
            } else {
                if ($attachment == "Tous") {
                    $drivers = $doctrine->getRepository(Volunteer::class)->findDriversForSelection();
                } else {
                    $drivers = $doctrine->getRepository(Volunteer::class)->findDriversForSelection($attachment);
                }
            }
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/drivers/drivers.html.twig', [
                    'pLine' => $pLine,
                    'drivers' => $drivers,
                    'search' => '',
                    'attachment' => $attachment
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
            if ($lastDriver) {
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/selection/drivers/cell.html.twig', [
                        'driver' => $lastDriver,
                        'active' => false,
                    ])->getContent(),
                    'select-driver-' . $lastDriver->getId()
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
                'select-driver-' . $driver->getId()
            );

            $ajaxResponse->addView(
                $driver->getDisplayName(),
                'selected-driver-' . $pLineId
            );

            $this->addFlash('success', 'Conducteur sélectionné');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/requests/selection/{pLineId}/{filter}', name: 'app_volunteer_planning_get_requests_selection')]
    public function getRequestsSelection(Request $request, ManagerRegistry $doctrine, int $pLineId, int $filter): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $attachment = $pLine->getAttachment();
            if ($attachment == "Tous") {
                $deliverys = $doctrine->getRepository(Delivery::class)->getPlanningSelection();
                $removals = $doctrine->getRepository(Removal::class)->getPlanningSelection();
            } else {
                $deliverys = $doctrine->getRepository(Delivery::class)->getPlanningSelection($attachment);
                $removals = $doctrine->getRepository(Removal::class)->getPlanningSelection($attachment);
            }

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

    #[Route('/volunteer/planning/add/demand/{type}/{demandId}/{pLineId}', name: 'app_volunteer_planning_add_removal')]
    public function addRemoval(Request $request, ManagerRegistry $doctrine, String $type, int $demandId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            if ($type == 'removal') {
                $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $demandId]);
                $removal->setPlanningLine($pLine);
                $removal->setState(1);
                $removal->setDatePlanified($removal->generatePlanifiedDate());
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
                    'select-removal-' . $removal->getId()
                );
            } else {
                $delivery = $doctrine->getRepository(Delivery::class)->findOneBy(['id' => $demandId]);
                $delivery->setPlanningLine($pLine);
                $delivery->setDatePlanified($delivery->generatePlanifiedDate());
                $delivery->setState(1);
                $em = $doctrine->getManager();
                $em->persist($delivery);
                $em->flush();
                $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
                $ajaxResponse = new AjaxResponse('volunteer/planning');
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/selection/requests/deliveryCell.html.twig', [
                        'pLine' => $pLine,
                        'delivery' => $delivery,
                        'active' => true,
                    ])->getContent(),
                    'select-delivery-' . $delivery->getId()
                );
            }


            $displayRequests = '';
            foreach ($pLine->getRemovals() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            foreach ($pLine->getDeliverys() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-requests-' . $pLineId
            );

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/remove/demand/{type}/{demandId}/{pLineId}', name: 'app_volunteer_planning_remove_removal')]
    public function removeRemoval(Request $request, ManagerRegistry $doctrine, String $type, int $demandId, int $pLineId): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            if ($type == 'removal') {
                $removal = $doctrine->getRepository(Removal::class)->findOneBy(['id' => $demandId]);
                $removal->setPlanningLine(null);
                $removal->setState(0);
                $removal->setDatePlanified(null);
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
                    'select-removal-' . $removal->getId()
                );
            } else {
                $delivery = $doctrine->getRepository(Delivery::class)->findOneBy(['id' => $demandId]);
                $delivery->setPlanningLine(null);
                $delivery->setDatePlanified(null);
                $delivery->setState(0);
                $em = $doctrine->getManager();
                $em->persist($delivery);
                $em->flush();
                $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);

                $ajaxResponse = new AjaxResponse('volunteer/planning');
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/selection/requests/deliveryCell.html.twig', [
                        'pLine' => $pLine,
                        'delivery' => $delivery,
                        'active' => false,
                    ])->getContent(),
                    'select-delivery-' . $delivery->getId()
                );
            }

            $displayRequests = '';
            foreach ($pLine->getRemovals() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            foreach ($pLine->getDeliverys() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-requests-' . $pLineId
            );

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/get/companions/selection/{pLineId}/{filter}', name: 'app_volunteer_planning_get_companions_selection')]
    public function getCompanionsSelection(Request $request, ManagerRegistry $doctrine, int $pLineId, int $filter): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            $attachment = $pLine->getAttachment();
            if ($attachment == "Tous") {
                $companions = $doctrine->getRepository(Volunteer::class)->findVolunteersForSelection();
            } else {
                $companions = $doctrine->getRepository(Volunteer::class)->findVolunteersForSelection($attachment);
            }
            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/selection/companions/companions.html.twig', [
                    'pLine' => $pLine,
                    'companions' => $companions,
                    'search' => '',
                    'attachment' => $attachment
                ])->getContent(),
                'modal-content'
            );
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/companion/add/{companionId}/{pLineId}', name: 'app_volunteer_planning_companion_add')]
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
                'select-companion-' . $companion->getId()
            );

            $displayRequests = '';
            foreach ($pLine->getCompanions() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            foreach ($pLine->getDeliverys() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-companions-' . $pLineId
            );

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/companion/remove/{companionId}/{pLineId}', name: 'app_volunteer_planning_companion_remove')]
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
                'select-companion-' . $companion->getId()
            );

            $displayRequests = '';
            foreach ($pLine->getCompanions() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            foreach ($pLine->getDeliverys() as $request) {
                $displayRequests = $displayRequests . $request->getDisplayName() . '<br/>';
            }
            $ajaxResponse->addView(
                $displayRequests,
                'selected-companions-' . $pLineId
            );

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/line/valid/get/form/{id}/{filter}', name: 'app_volunteer_planning_line_valid_get_form')]
    public function lineValidGetForm(Request $request, ManagerRegistry $doctrine, int $id, int $filter): Response
    {
        $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $id]);
        if ($request->isXmlHttpRequest()) {
            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $ajaxResponse->addView(
                $this->render('volunteer/planning/line/formValidate.html.twig', [
                    'pLine' => $pLine,
                    'filter' => $filter
                ])->getContent(),
                'modal-content'
            );

            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/line/valid/validate/{id}/{filter}', name: 'app_volunteer_planning_line_valid_validate')]
    public function lineValidValidate(Request $request, ManagerRegistry $doctrine, int $id, int $filter): Response
    {
        if ($request->isXmlHttpRequest()) {
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $id]);
            $pLine->setValid(true);
            $em = $doctrine->getManager();
            foreach ($pLine->getRemovals() as $removal) {
                $weight = $request->get('weightRemoval' . $removal->getId());
                if ($weight > 0) {
                    $removal->setWeight($weight);
                    $removal->setState(2);
                    $removal->setDateRealized(new \DateTime());
                    $em->persist($removal);
                } else {
                    $pLine->removeRemoval($removal);
                    $removal->setState(0);
                    $removal->setplanningLine(null);
                    $em->persist($removal);
                }
            }
            foreach ($pLine->getDeliverys() as $delivery) {
                $weight = $request->get('weightDelivery' . $delivery->getId());
                if ($weight > 0) {
                    $delivery->setWeight($weight);
                    $delivery->setState(2);
                    $delivery->setDateRealized(new \DateTime());
                    $em->persist($delivery);
                } else {
                    $pLine->removeDelivery($delivery);
                    $delivery->setState(0);
                    $delivery->setplanningLine(null);
                    $em->persist($delivery);
                }
            }
            $em->persist($pLine);
            $em->flush();

            $ajaxResponse = new AjaxResponse('volunteer/planning');

            $ajaxResponse->addView(
                $this->render('volunteer/planning/line.html.twig', [
                    'pLine' => $pLine,
                    'filter' => $filter
                ])->getContent(),
                'pLine' . $pLine->getId()
            );
            $this->addFlash('success', 'Ligne validée');
            $ajaxResponse->setFlashMessageView($this->renderView('flashMessages.html.twig'));
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    #[Route('/volunteer/planning/line/voucher/generate/{id}/{filter}', name: 'app_volunteer_planning_line_voucher_generate')]
    public function lineVoucheGenerate(Request $request, ManagerRegistry $doctrine, Environment $twig, int $id, int $filter)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $id]);

        $dayDate = $pLine->getPlanningWeek()->getMondayDate();
        if ($pLine->getDay() > 1) {
            $dayDate->add(new DateInterval('P' . intdiv($pLine->getDay(), 2) . 'D'));
        }

        $dompdf->loadHtml($this->renderView('volunteer\planning\voucherPdf.html.twig', [
            'pLine' => $pLine,
            'dayDate' => $dayDate,
            'filter' => $filter
        ]));

        $dompdf->render();

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        // Définir le type de contenu PDF
        header("Content-Type: application/pdf");
        // Indiquer au navigateur de télécharger le PDF plutôt que de l'afficher dans le navigateur
        header("Content-Disposition: attachment; filename=\"votre_fichier.pdf\"");
        // Sortir le contenu du PDF
        $dompdf->stream("ligne" . $pLine->getId() . ".pdf", [
            "Attachment" => true
        ]);

        die();
        return $this->render('volunteer\planning\voucherPdf.html.twig', [
            'pLine' => $pLine,
            'dayDate' => $dayDate,
        ]);
    }

    #[Route('/volunteer/planning/week/change/{oldMonday}/{action}/{filter}', name: 'app_volunteer_week_change')]
    public function weekChange(Request $request, ManagerRegistry $doctrine, string $oldMonday, string $action, int $filter): Response
    {
        if ($request->isXmlHttpRequest()) {
            $oldMonday = str_replace("-", "/", $oldMonday);
            $oldMonday = new \DateTime($oldMonday);
            if ($action == "previous") {
                $monday = $oldMonday->modify('-1 week');
            } else {
                $monday = $oldMonday->modify('+1 week');
            }
            $year = $monday->format("Y");
            $week = $monday->format("W");
			if(($monday->format("d") == 30 || $monday->format("d") == 31 ) && $monday->format("m")==12){
				$year = $year+1;
			}
            $week_start = $monday;
            $pWeek = $doctrine->getRepository(PlanningWeek::class)->findOneBy(['year' => $year, 'number' => $week]);
            if (is_null($pWeek)) {
                $pWeek = (new PlanningWeek())
                    ->setYear($year)
                    ->setnumber($week)
                    ->setMondayDate($week_start->setISODate($year, $monday->format("W")));
                $em = $doctrine->getManager();
                $em->persist($pWeek);
                $em->flush();
            }

            $planningManager = new Manager($doctrine, $pWeek, $filter);

            $ajaxResponse = new AjaxResponse('volunteer/planning');
            $ajaxResponse->addView(
                $this->render('volunteer/planning/content.html.twig', [
                    'pWeek' => $pWeek,
                    'linesPerDay' => $planningManager->getLinesPerDay(),
                    'filter' => $filter
                ])->getContent(),
                'body-interface'
            );
            $ajaxResponse->setRedirectTo(false);
            return $ajaxResponse->generateContent();
        }
        return $this->redirectToRoute('app_volunteer_planning');
    }

    //type = driver or ac
    #[Route('/volunteer/planning/search/companion/{pLineId}/{attachment}', name: 'app_volunteer_planning_search_companion')]
    public function searchCompanion(Request $request, ManagerRegistry $doctrine, int $pLineId, string $attachment): Response
    {
        if ($request->isXmlHttpRequest()) {

            $search = $request->query->get('search', '');
            $type = $request->query->get('type', '');
            $pLine = $doctrine->getRepository(PlanningLine::class)->findOneBy(['id' => $pLineId]);
            if ($type === 'driver' && $pLine->getVehicle()?->getHgv()) {
                $type = 'hgvDriver';
            }
            $query = $doctrine->getRepository(Volunteer::class)->getVolunteerByName($attachment, $search, $type);
            $companions = $query->getResult();

            $ajaxResponse = new AjaxResponse('volunteer/provider');
            if ($type === 'driver' || $type === 'hgvDriver') {
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/component/volunteerGrid.html.twig', [
                        'pLine' => $pLine,
                        'drivers' => $companions,
                        'search' => $search,
                    ])->getContent(),
                    'modal-body'
                );
            } else {
                $ajaxResponse->addView(
                    $this->render('volunteer/planning/component/volunteerGrid.html.twig', [
                        'pLine' => $pLine,
                        'companions' => $companions,
                        'search' => $search
                    ])->getContent(),
                    'modal-body'
                );
            }
            return $ajaxResponse->generateContent();
        }

        return $this->redirectToRoute('app_volunteer_planning');
    }
}
