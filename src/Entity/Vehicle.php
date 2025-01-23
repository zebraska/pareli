<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 100)]
    private $name;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $hgv;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $attachment;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enable;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getHgv(): ?bool
    {
        return $this->hgv;
    }

    public function setHgv(?bool $hgv): self
    {
        $this->hgv = $hgv;

        return $this;
    }
    
    public function getDisplayName(): ?string
    {
        return ($this->hgv) ? 'PL '.$this->name : $this->name;
    }

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(?string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(?bool $enable): self
    {
        $this->enable = $enable;

        return $this;
    }
}
