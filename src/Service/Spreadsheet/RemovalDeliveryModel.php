<?php

namespace App\Service\Spreadsheet;

use App\Entity\Removal;
use App\Entity\Delivery;
use Doctrine\Persistence\ManagerRegistry;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RemovalDeliveryModel {
    
    private $doctrine;
    private $removals;
    private $deliverys;
    private $spreadsheet;


    public function __construct(ManagerRegistry $doctrine, \DateTime $dateStart, \DateTime $dateEnd) {
        $this->doctrine = $doctrine;
        $this->removals = $doctrine->getRepository(Removal::class)->getAllRemovalsByInterval($dateStart, $dateEnd);
        $this->deliverys = $doctrine->getRepository(Delivery::class)->getAllDeliverysByInterval($dateStart, $dateEnd);
        $this->spreadsheet = self::generateSpreadsheet();
    }
    
    public function generateSpreadsheet(){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getColumnDimension('A')->setWidth(2.23,'cm');
        $sheet->getColumnDimension('B')->setWidth(8,'cm');
        $sheet->getColumnDimension('C')->setWidth(8,'cm');
        $sheet->getColumnDimension('D')->setWidth(8,'cm');
        $sheet->setCellValue('A1', 'Date');
        $sheet->setCellValue('B1', 'Instructions');
        $sheet->setCellValue('C1', 'Fournisseur');
        $sheet->setCellValue('D1', 'Contenants');
        
        $sheet->setCellValue('A2', 'Enlèvements');
        $sheet->mergeCells('A2:D2');
        
        //contains current row progression
        $y = 3;
        
        foreach($this->removals as $removal) {
            $sheet->setCellValue([1,$y], $removal->getDateCreate()->format('d-m-Y'));
            $sheet->setCellValue([2,$y], $removal->getComment());
            $sheet->setCellValue([3,$y], $removal->getDisplayName());
            //$sheet->setCellValue([4,$y], $removal->getRemovalContainerQuantities());
            $y++;
        }
        
        $sheet->setCellValue([1,$y], 'Livraisons');
        $sheet->mergeCells([1,$y,4,$y]);
        $y++;
        
        foreach($this->deliverys as $delivery) {
            $sheet->setCellValue([1,$y], $delivery->getDateCreate()->format('d-m-Y'));
            $sheet->setCellValue([2,$y], $delivery->getComment());
            $sheet->setCellValue([3,$y], $delivery->getDisplayName());
            //$sheet->setCellValue([4,$y], $removal->getRemovalContainerQuantities());
            $y++;
        }
        
        
        return $spreadsheet;
    }
    
    //doesn't work
    public function sortByDate(){
        $requests = [];
        if (empty($this->removals)){
            $requests = $this->deliverys;
            unset($this->deliverys);
            return $requests;
        }
        if (empty($this->deliverys)){
            $requests = $this->removals;
            unset($this->removals);
            return $requests;
        }
        foreach($this->removals as $keyR => $removal){
            if (!empty($this->deliverys)){
                foreach($this->deliverys as $keyD => $delivery){
                    // removal more ancient than delivery
                    if (date_diff($removal->getDateCreate(), $delivery->getDateCreate())->invert === 0){
                        $requests[] = $delivery;
                        unset($this->deliverys[$keyD]);
                    // delivery more ancient than removal    
                    } else {
                        $requests[] = $removal;
                        unset($this->removals[$keyR]);
                    }
                }
            } else {
                array_merge($requests, $this->removals);
                unset($this->removals);
            }    
        }
        
        return $requests;
    }
    
    public function getRemovals() {
        return $this->removals;
    }

    public function getDeliverys() {
        return $this->deliverys;
    }

    public function getSpreadsheet() {
        return $this->spreadsheet;
    }

    
}

