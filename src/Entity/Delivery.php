<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;

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
    private $planningLine;

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
}
