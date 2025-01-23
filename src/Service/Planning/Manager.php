<?php

namespace App\Service\Planning;

use App\Entity\PlanningLine;
use App\Entity\PlanningWeek;
use Doctrine\Persistence\ManagerRegistry;

class Manager {
    
    private $doctrine;
    private $pWeek;
    private $linesPerDay;
    
    public function __construct(ManagerRegistry $em,PlanningWeek $pWeek,int $filter) {
        
        $this->doctrine = $em;
        $this->pWeek = $pWeek;
        $this->linesPerDay = [];
        self::generateLinesPerDay($filter);
        
    }
    
    public function setDoctrine($doctrine): void {
        $this->doctrine = $doctrine;
    }

    public function setPWeek($pWeek): void {
        $this->pWeek = $pWeek;
    }
    
    public function setLinesPerDay($linesPerDay): void {
        $this->linesPerDay = $linesPerDay;
    }
    
    public function generateLinesPerDay(int $filter) {
        
        $this->linesPerDay[0] = ['title' => 'Lundi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,0,$filter)];
        $this->linesPerDay[1] = ['title' => 'Lundi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,1,$filter)];

        $this->linesPerDay[2] = ['title' => 'Mardi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,2,$filter)];
        $this->linesPerDay[3] = ['title' => 'Mardi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,3,$filter)];

        $this->linesPerDay[4] = ['title' => 'Mercredi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,4,$filter)];
        $this->linesPerDay[5] = ['title' => 'Mercredi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,5,$filter)];

        $this->linesPerDay[6] = ['title' => 'Jeudi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,6,$filter)];
        $this->linesPerDay[7] = ['title' => 'Jeudi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,7,$filter)];

        $this->linesPerDay[8] = ['title' => 'Vendredi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,8,$filter)];
        $this->linesPerDay[9] = ['title' => 'Vendredi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findForPlanning($this->pWeek,9,$filter)];
        
    }
    
    public function getDoctrine() {
        return $this->doctrine;
    }

    public function getPWeek() {
        return $this->pWeek;
    }

    public function getLinesPerDay() {
        return $this->linesPerDay;
    }
    
    
}

//$dateStart = new \DateTime;
//$dateEnd = new \DateTime;
////$dateEnd->modify('+1 year');
//echo $dateStart->format('Y-M-d').' '.$dateEnd->format('Y-M-d');
//$years = Manager::getAllYearsBetweenDates($dateStart, $dateEnd);
//echo '<br/>';
//foreach($years as $year){
//    echo strval($year);
//}
