<?php

namespace App\Service\Planning;

use App\Entity\PlanningLine;
use App\Entity\PlanningWeek;
use Doctrine\Persistence\ManagerRegistry;

class Manager {
    
    private $doctrine;
    private $pWeek;
    private $linesPerDay;
    
    public function __construct(ManagerRegistry $em,PlanningWeek $pWeek) {
        
        $this->doctrine = $em;
        $this->pWeek = $pWeek;
        $this->linesPerDay = [];
        self::generateLinesPerDay();
        
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
    
    public function generateLinesPerDay() {
        
        $this->linesPerDay[0] = ['title' => 'Lundi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 0])];
        $this->linesPerDay[1] = ['title' => 'Lundi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 1])];

        $this->linesPerDay[2] = ['title' => 'Mardi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 2])];
        $this->linesPerDay[3] = ['title' => 'Mardi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 3])];

        $this->linesPerDay[4] = ['title' => 'Mercredi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 4])];
        $this->linesPerDay[5] = ['title' => 'Mercredi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 5])];

        $this->linesPerDay[6] = ['title' => 'Jeudi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 6])];
        $this->linesPerDay[7] = ['title' => 'Jeudi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 7])];

        $this->linesPerDay[8] = ['title' => 'Vendredi matin', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 8])];
        $this->linesPerDay[9] = ['title' => 'Vendredi après-midi', 'lines' => $this->doctrine->getRepository(PlanningLine::class)->findBy(['planningWeek' => $this->pWeek, 'day' => 9])];
        
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
