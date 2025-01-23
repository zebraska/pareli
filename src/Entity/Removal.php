<?php

namespace App\Entity;

use App\Repository\RemovalRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RemovalRepository::class)]
class Removal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    private $dateCreate;

    #[ORM\Column(type: 'date')]
    private $dateRequest;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    #[ORM\ManyToOne(targetEntity: Provider::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $provider;

    #[ORM\OneToMany(mappedBy: 'removal', targetEntity: RemovalContainerQuantity::class, orphanRemoval: true)]
    private $removalContainerQuantities;

    #[ORM\Column(type: 'integer')]
    private $state;

    #[ORM\ManyToOne(targetEntity: PlanningLine::class, inversedBy: 'removals')]
    #[ORM\JoinColumn(onDelete:"SET NULL")]
    private $planningLine;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $weight;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $dateRealized;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $datePlanified;

    public function __construct()
    {
        $this->removalContainerQuantities = new ArrayCollection();
    }

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

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function setProvider(?Provider $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * @return Collection<int, RemovalContainerQuantity>
     */
    public function getRemovalContainerQuantities(): Collection
    {
        return $this->removalContainerQuantities;
    }

    public function addRemovalContainerQuantity(RemovalContainerQuantity $removalContainerQuantity): self
    {
        if (!$this->removalContainerQuantities->contains($removalContainerQuantity)) {
            $this->removalContainerQuantities[] = $removalContainerQuantity;
            $removalContainerQuantity->setRemoval($this);
        }

        return $this;
    }

    public function removeRemovalContainerQuantity(RemovalContainerQuantity $removalContainerQuantity): self
    {
        if ($this->removalContainerQuantities->removeElement($removalContainerQuantity)) {
            // set the owning side to null (unless already changed)
            if ($removalContainerQuantity->getRemoval() === $this) {
                $removalContainerQuantity->setRemoval(null);
            }
        }

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
        return $this->provider->getName();
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function generatePlanifiedDate(): DateTime
    {
        return $this->planningLine->getPlanningWeek()->getMondayDate()->modify('+'.floor($this->planningLine->getDay()/2).' day');
    }

    public function getDateRealized(): ?\DateTimeInterface
    {
        return $this->dateRealized;
    }

    public function setDateRealized( ?\DateTimeInterface $dateRealized): self
    {
        $this->dateRealized = $dateRealized;

        return $this;
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
    
}
