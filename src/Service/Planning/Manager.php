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
    
    public static function getSortedPWeeks($pWeeks, \DateTime $dateStart, \DateTime $dateEnd) {
        
        $dateStart = self::getNextMondayIfWE($dateStart);
        $dateEnd = self::getPreviousFridayIfWE($dateEnd);
        $lastPWeekFri = end($pWeeks)->getMondayDate();
        $lastPWeekFri->modify('+4 day');
        $dateIntervalMonStart = date_diff($pWeeks[0]->getMondayDate(), $dateStart);
        $dateIntervalEndFri = date_diff($dateEnd, $lastPWeekFri);
        dump($dateIntervalMonStart, $dateIntervalEndFri);
        //when dateStart is after the mondayDate of the first pWeek we cutoff PLines
        if ($dateIntervalMonStart->invert === 0){
            $pLinesArray = $pWeeks[0]->getPlanningLines();            
            foreach($pLinesArray as $pLine){
                if($pLine->getDay() + 1 <= $dateIntervalMonStart->d * 2){
                    unset($pLine);
                }
            }
            $pWeeks[0]->setPlanningLines($pLinesArray);
        }
        //when dateEnd is before the friday of the last pWeek we cutoff PLines
        if ($dateIntervalEndFri->invert === 0){
            $pLinesArray = end($pWeeks)->getPlanningLines();
            foreach($pLinesArray as $pLine){
                if($pLine->getDay() +1 >= $dateIntervalMonStart->d * 2){
                    unset($pLine);
                }
                end($pWeeks)->setPlanningLines($pLinesArray);
        }
        
        }
        
        return $pWeeks;
    }
    
    //useful to get business day from getSortedPWeek dateEnd
    public static function getPreviousFridayIfWE(\DateTime $date) {
        
        if ($date->format('w') === 0){
            $date->modify('-2 day');
        }
        if ($date->format('w') === 6){
            $date->modify('-1 day');
        }
        
        return $date;
    }
    
    //useful to get business day from getSortedPWeek dateStart
    public static function getNextMondayIfWE(\DateTime $date) {
        
        if ($date->format('w') === 0){
            $date->modify('+1 day');
        }
        if ($date->format('w') === 6){
            $date->modify('+2 day');
        }
        
        return $date;
    }
    
    public static function getAllYearsBetweenDates(\DateTime $dateStart, \DateTime $dateEnd){
        
        $years = [$dateStart->format('Y')];
        $dateInterval = date_diff($dateStart, $dateEnd);
        if ($dateInterval->y > 1){
            for($i = 1; $i < $dateInterval->y; $i++ ) {
            $dateStart->modify('+1 year');
            $years[] = $dateStart->format('Y');
            }
        }
        if ($dateStart->format('Y') !== $dateEnd->format('Y')){
            $years[] = $dateEnd->format('Y');
        }
        
        return $years;
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
