<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'date')]
    private $dateCreate;

    #[ORM\Column(type: 'date')]
    private $dateRequest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Recycler::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $recycler;

    #[ORM\Column(type: 'integer')]
    private $state;

    #[ORM\ManyToOne(targetEntity: PlanningLine::class, inversedBy: 'deliverys')]
    #[ORM\JoinColumn(onDelete:"SET NULL")]
    private $planningLine;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $weight;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $datePlanified;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $dateRealized;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTimeInterface $dateCreate): self
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function getDateRequest(): ?\DateTimeInterface
    {
        return $this->dateRequest;
    }

    public function setDateRequest(\DateTimeInterface $dateRequest): self
    {
        $this->dateRequest = $dateRequest;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getRecycler(): ?Recycler
    {
        return $this->recycler;
    }

    public function setRecycler(?Recycler $recycler): self
    {
        $this->recycler = $recycler;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }
    
    public function getStateString(): String
    {
        switch ($this->state){
                case 0:
                    $stateString = 'À Planifier';
                    break;
                case 1:
                    $stateString = 'Planifiée';
                    break;
                case 2:
                    $stateString = 'Réalisée';                    
                    break;
            }
            
            return $stateString;
    }

    public function getPlanningLine(): ?PlanningLine
    {
        return $this->planningLine;
    }

    public function setPlanningLine(?PlanningLine $planningLine): self
    {
        $this->planningLine = $planningLine;

        return $this;
    }

    public function getDisplayName(): String
    {
        return $this->recycler->getName();
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function generatePlanifiedDate(): DateTime
    {
        return $this->planningLine->getPlanningWeek()->getMondayDate()->modify('+'.floor($this->planningLine->getDay()/2).' day');
    }

    public function getDatePlanified(): ?\DateTimeInterface
    {
        return $this->datePlanified;
    }

    public function setDatePlanified(?\DateTimeInterface $datePlanified): self
    {
        $this->datePlanified = $datePlanified;

        return $this;
    }

    public function getDateRealized(): ?\DateTimeInterface
    {
        return $this->dateRealized;
    }

    public function setDateRealized(?\DateTimeInterface $dateRealized): self
    {
        $this->dateRealized = $dateRealized;

        return $this;
    }
    
}
