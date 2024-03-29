<?php

namespace App\Service\Spreadsheet;

use App\Entity\Removal;
use App\Entity\Delivery;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\String_;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class RemovalDeliveryModel {
    
    private $doctrine;
    private $removals;
    private $deliverys;
    private $spreadsheet;


    public function __construct(ManagerRegistry $doctrine, \DateTime $dateStart, \DateTime $dateEnd, String $stateFilter) {
        $this->doctrine = $doctrine;
        $this->removals = $doctrine->getRepository(Removal::class)->getAllRemovalsByInterval($dateStart, $dateEnd, $stateFilter)->getResult();
        $this->deliverys = $doctrine->getRepository(Delivery::class)->getAllDeliverysByInterval($dateStart, $dateEnd, $stateFilter)->getResult();
        $this->spreadsheet = self::generateSpreadsheet($dateStart, $dateEnd);
    }
    
    public function generateSpreadsheet(\DateTime $dateStart, \DateTime $dateEnd){
        $spreadsheet = new Spreadsheet();        
        $spreadsheet->getDefaultStyle()->getFont()->setSize(10);
        $sheet = $spreadsheet->getActiveSheet();   
        $sheet->getColumnDimension('A')->setWidth(2.23,'cm');
        $sheet->getColumnDimension('B')->setWidth(8,'cm');
        $sheet->getColumnDimension('C')->setWidth(8,'cm');
        $sheet->getColumnDimension('D')->setWidth(2.23,'cm');
        $sheet->getColumnDimension('E')->setWidth(2.23,'cm');      
        $sheet->setCellValue('A1', 'Export des demandes du '.$dateStart->format('d/m/y').' au '.$dateEnd->format('d/m/y'));
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray(self::generateH1StyleArray());
        $sheet->getRowDimension('1')->setRowHeight(1, 'cm');                
        
        $sheet->setCellValue('A2', 'Enlèvements');
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2:E2')->applyFromArray(self::generateH2StyleArray());
        
        $sheet->setCellValue('A3', 'Date');
        $sheet->setCellValue('B3', 'Commentaires');        
        $sheet->setCellValue('C3', 'Fournisseur');
        $sheet->setCellValue('D3', 'Poids');
        $sheet->setCellValue('E3', 'État');
        $sheet->getStyle('A3:E3')->applyFromArray(self::generateH3StyleArray());
        
        //contains current row progression
        $y = 4;
                
        foreach($this->removals as $removal) {
            $weightString = '';
            $providerString = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
            $providerName = $providerString->createTextRun($removal->getDisplayName());
            $providerName->getFont()->setBold(true);
            $providerString->createText("\n".$removal->getProvider()->getDisplayAddress());
            if($removal->getState() === 2){
                $weightString = strval($removal->getWeight()).' kg';
            }
            $sheet->setCellValue([1,$y], $removal->getDateCreate()->format('d-m-Y'));
            $sheet->setCellValue([2,$y], $removal->getComment());
            $sheet->setCellValue([3,$y], $providerString);            
            $sheet->setCellValue([4,$y], $weightString);
            $sheet->setCellValue([5,$y], $removal->getStateString());
            $sheet->getRowDimension("$y")->setRowHeight(self::calculateRowHeight($removal), 'cm');                                
            if ($y % 2 === 0){
                $sheet->getStyle([1,$y,6,$y])->getFill()->SetFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
            }
            $y++;            
        }
        
        $sheet->getStyle([1,4,6,$y-1])->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle([2,4,2,$y-1])
            ->getAlignment()->setWrapText(true);
        $sheet->getStyle([3,4,3,$y-1])
            ->getAlignment()->setWrapText(true);
        $sheet->getStyle([6,4,6,$y-1])
            ->getAlignment()->setWrapText(true);
        
        $sheet->setCellValue([1,$y], 'Livraisons');
        $sheet->mergeCells([1,$y,6,$y]);
        $sheet->getStyle([1,$y,6,$y])->applyFromArray(self::generateH2StyleArray());
        $y++;
        
        $sheet->setCellValue([1,$y], 'Date');
        $sheet->setCellValue([2,$y], 'Commentaires');
        $sheet->setCellValue([3,$y], 'Fournisseur');
        $sheet->setCellValue([4,$y], 'Poids');
        $sheet->setCellValue([5,$y], 'État');
        $sheet->getStyle([1,$y,6,$y])->applyFromArray(self::generateH3StyleArray());
        $y++;
        
        $yTopDelivery = $y;
        foreach($this->deliverys as $delivery) {
            $weightString = '';
            $providerString = new \PhpOffice\PhpSpreadsheet\RichText\RichText();
            $providerName = $providerString->createTextRun($delivery->getDisplayName());
            $providerName->getFont()->setBold(true);
            $providerString->createText("\n".$delivery->getRecycler()->getDisplayAddress());
            if($delivery->getState() === 2){
                $weightString = strval($removal->getWeight()).' kg';
            }
            $sheet->setCellValue([1,$y], $delivery->getDateCreate()->format('d-m-Y'));
            $sheet->setCellValue([2,$y], $delivery->getComment());
            $sheet->setCellValue([3,$y], $providerString);
            $sheet->setCellValue([4,$y], $weightString);
            $sheet->setCellValue([5,$y], $delivery->getStateString());
            $sheet->getRowDimension("$y")->setRowHeight(self::calculateRowHeight($removal), 'cm');
            if ($y % 2 === 0){
                $sheet->getStyle([1,$y,6,$y])->getFill()->SetFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
            }
            $y++;
        }               
        
        $sheet->getStyle([1,$yTopDelivery,6,$y-1])->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle([2,4,2,$y-1])
            ->getAlignment()->setWrapText(true);
        $sheet->getStyle([3,4,3,$y-1])
            ->getAlignment()->setWrapText(true);
        $sheet->getStyle([6,4,6,$y-1])
            ->getAlignment()->setWrapText(true);
        
        $sheet->getStyle([1,1,6,$y-1])->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getPageSetup()->setPrintArea('A1:F'.$y-1);
       
        return $spreadsheet;
    }
    
    public function completeSpreadsheet(){
        
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->getColumnDimension('F')->setWidth(4,'cm');
        $sheet->getColumnDimension('G')->setWidth(4,'cm');
        $sheet->getColumnDimension('H')->setWidth(4,'cm');
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        
        $sheet->setCellValue('F3', 'Véhicule');
        $sheet->setCellValue('G3', 'Conducteur');        
        $sheet->setCellValue('H3', 'Accompagnant');
        $sheet->getStyle('F3:H3')->applyFromArray(self::generateH3StyleArray());
        
        $y = 4;
                
        foreach($this->removals as $removal) {            
            $planningLine = $removal->getPlanningLine();
            if ($planningLine !== null){
                $companionsString = '';
                foreach($planningLine->getCompanions() as $key => $companion){
                    $companionsString .= $companion->getDisplayName();
                    if($key + 1 !== count($planningLine->getCompanions())){$companionsString .= "\n";}
                }
                $sheet->setCellValue([6,$y], $planningLine->getVehicle()?$planningLine->getVehicle()->getDisplayName():'Non renseigné');
                $sheet->setCellValue([7,$y], $planningLine->getDriver()?$planningLine->getDriver()->getDisplayName():'Non renseigné');
                $sheet->setCellValue([8,$y], $companionsString);
            }
            if ($y % 2 === 0){
                $sheet->getStyle([6,$y,8,$y])->getFill()->SetFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
            }
            $y++;
        }
        $sheet->getStyle([6,4,8,$y-1])->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle([8,4,8,$y-1])
            ->getAlignment()->setWrapText(true);
        
        $sheet->mergeCells([1,$y,8,$y]);
        $y++;
        
        $sheet->setCellValue([6,$y], 'Véhicule');
        $sheet->setCellValue([7,$y], 'Conducteur');        
        $sheet->setCellValue([8,$y], 'Accompagnant');
        $sheet->getStyle([6,$y,8,$y])->applyFromArray(self::generateH3StyleArray());
        $y++;
        
        $yTopDelivery = $y;
        foreach($this->deliverys as $delivery) {
            $planningLine = $delivery->getPlanningLine();
            if ($planningLine !== null){
                $companionsString = '';
                foreach($planningLine->getCompanions() as $key => $companion){
                    $companionsString .= $companion->getDisplayName();
                    if($key + 1 !== count($planningLine->getCompanions())){$companionsString .= "\n";}
                }
                $sheet->setCellValue([6,$y], $planningLine->getVehicle()?$planningLine->getVehicle()->getDisplayName():'Non renseigné');
                $sheet->setCellValue([7,$y], $planningLine->getDriver()?$planningLine->getDriver()->getDisplayName():'Non renseigné');
                $sheet->setCellValue([8,$y], $companionsString);
            }
            if ($y % 2 === 0){
                $sheet->getStyle([6,$y,8,$y])->getFill()->SetFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDDDDD');
            }
            $y++;
        }
        
        $sheet->getStyle([6,$yTopDelivery,8,$y-1])->getBorders()->getVertical()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle([8,4,8,$y-1])
            ->getAlignment()->setWrapText(true);
        
        $sheet->getStyle([1,1,8,$y-1])->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getPageSetup()->setPrintArea('A1:H'.$y-1);
        
        
    }
    
    private static function calculateRowHeight($removal) {
        
        if(get_class($removal) === 'Removal'){       
            if(count($removal->getRemovalContainerQuantities()) > intdiv(strlen($removal->getComment()), 60)) {
                $height = 0.4 * count($removal->getRemovalContainerQuantities());
            } else {
                $height = 0.4 * intdiv(strlen($removal->getComment()), 60);
            }
        } else {
            $height = 0.4 * intdiv(strlen($removal->getComment()), 60);
        }
        
        if($height > 1.2) {
            return $height;
        } else {
            return 1.2;
        }
    }
    
    private static function generateH1StyleArray() {
        
        $styleArray = [
           'font' => [
               'size' => 15,
               'bold' => true,
               'italic' => true,
               'color' => [
                   'argb' => 'FFFFFFFF'
               ]
           ],
           'alignment' => [
               'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
               'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
           ],
           'fill' => [
               'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
               'startColor' => [
                   'argb' => 'FF245682'
               ]
           ]
        ];
        
        return $styleArray;
    }
    
    private static function generateH2StyleArray() {
        
        $styleArray = [
           'font' => [
               'size' => 12,
               'italic' => true,
               'color' => [
                   'argb' => 'FFFFFFFF'
               ]
           ],
           'alignment' => [
               'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
               'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
           ],
           'fill' => [
               'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
               'startColor' => [
                   'argb' => 'FF212529'
               ]
           ]
        ];
        
        return $styleArray;
    }
    
    private static function generateH3StyleArray() {
        
        $styleArray = [
           'font' => [
               'size' => 11,
               'bold' => true,               
           ],
           'alignment' => [
               'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
               'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
           ],
           'borders' => [               
               'allBorders' => [
                   'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN                   
               ]
           ]
        ];
        
        return $styleArray;
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

