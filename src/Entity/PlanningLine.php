<?php

namespace App\Entity;

use App\Repository\PlanningLineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningLineRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PlanningLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToMany(mappedBy: 'planningLine', targetEntity: Delivery::class)]
    private $deliverys;

    #[ORM\OneToMany(mappedBy: 'planningLine', targetEntity: Removal::class)]
    private $removals;

    #[ORM\ManyToMany(targetEntity: Volunteer::class)]
    #[ORM\JoinTable(name: "planning_line_companions")]
    private $companions;

    #[ORM\ManyToOne(targetEntity: Vehicle::class)]
    private $vehicle;

    #[ORM\ManyToOne(targetEntity: PlanningWeek::class, inversedBy: 'planningLines')]
    #[ORM\JoinColumn(nullable: false)]
    private $planningWeek;

    #[ORM\Column(type: 'integer')]
    private $day;

    #[ORM\ManyToOne(targetEntity: Volunteer::class)]
    private $driver;

    #[ORM\Column(type: 'boolean')]
    private $valid;

    public function __construct()
    {
        $this->deliverys = new ArrayCollection();
        $this->removals = new ArrayCollection();
        $this->companions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliverys(): Collection
    {
        return $this->deliverys;
    }

    public function addDelivery(Delivery $delivery): self
    {
        if (!$this->deliverys->contains($delivery)) {
            $this->deliverys[] = $delivery;
            $delivery->setPlanningLine($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): self
    {
        if ($this->deliverys->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getPlanningLine() === $this) {
                $delivery->setPlanningLine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Removal>
     */
    public function getRemovals(): Collection
    {
        return $this->removals;
    }

    public function addRemoval(Removal $removal): self
    {
        if (!$this->removals->contains($removal)) {
            $this->removals[] = $removal;
            $removal->setPlanningLine($this);
        }

        return $this;
    }

    public function removeRemoval(Removal $removal): self
    {
        if ($this->removals->removeElement($removal)) {
            // set the owning side to null (unless already changed)
            if ($removal->getPlanningLine() === $this) {
                $removal->setPlanningLine(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Volunteer>
     */
    public function getCompanions(): Collection
    {
        return $this->companions;
    }

    public function addCompanion(Volunteer $companion): self
    {
        if (!$this->companions->contains($companion)) {
            $this->companions[] = $companion;
        }

        return $this;
    }

    public function removeCompanion(Volunteer $companion): self
    {
        $this->companions->removeElement($companion);

        return $this;
    }

    public function getVehicle(): ?Vehicle
    {
        return $this->vehicle;
    }

    public function setVehicle(?Vehicle $vehicle): self
    {
        $this->vehicle = $vehicle;

        return $this;
    }

    public function getPlanningWeek(): ?PlanningWeek
    {
        return $this->planningWeek;
    }

    public function setPlanningWeek(?PlanningWeek $planningWeek): self
    {
        $this->planningWeek = $planningWeek;

        return $this;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getDriver(): ?Volunteer
    {
        return $this->driver;
    }

    public function setDriver(?Volunteer $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }
    
    //listener: onRemove set children removals and deliverys to state 0 - unplannified
    #[ORM\PreRemove]
    public function onRemove()
    {
        foreach($this->removals as $removal){
            $removal->setState(0);
        }
        foreach($this->deliverys as $delivery){
            $delivery->setState(0);
        }
    }
}
