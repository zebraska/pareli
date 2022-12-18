<?php

namespace App\Entity;

use App\Repository\PlanningWeekRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanningWeekRepository::class)]
class PlanningWeek
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $number;

    #[ORM\Column(type: 'integer')]
    private $year;

    #[ORM\Column(type: 'datetime')]
    private $mondayDate;

    #[ORM\OneToMany(mappedBy: 'planningWeek', targetEntity: PlanningLine::class)]
    private $planningLines;

    public function __construct()
    {
        $this->planningLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getMondayDate(): ?\DateTimeInterface
    {
        return $this->mondayDate;
    }

    public function setMondayDate(\DateTimeInterface $mondayDate): self
    {
        $this->mondayDate = $mondayDate;

        return $this;
    }

    /**
     * @return Collection<int, PlanningLine>
     */
    public function getPlanningLines(): Collection
    {
        return $this->planningLines;
    }

    public function addPlanningLine(PlanningLine $planningLine): self
    {
        if (!$this->planningLines->contains($planningLine)) {
            $this->planningLines[] = $planningLine;
            $planningLine->setPlanningWeek($this);
        }

        return $this;
    }

    public function removePlanningLine(PlanningLine $planningLine): self
    {
        if ($this->planningLines->removeElement($planningLine)) {
            // set the owning side to null (unless already changed)
            if ($planningLine->getPlanningWeek() === $this) {
                $planningLine->setPlanningWeek(null);
            }
        }

        return $this;
    }
}
