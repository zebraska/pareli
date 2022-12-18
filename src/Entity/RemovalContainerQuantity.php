<?php

namespace App\Entity;

use App\Repository\RemovalContainerQuantityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RemovalContainerQuantityRepository::class)]
class RemovalContainerQuantity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Container::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $container;

    #[ORM\Column(type: 'integer')]
    private $quantity;

    #[ORM\ManyToOne(targetEntity: Removal::class, inversedBy: 'removalContainerQuantities')]
    private $removal;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContainer(): ?Container
    {
        return $this->container;
    }

    public function setContainer(?Container $container): self
    {
        $this->container = $container;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getRemoval(): ?Removal
    {
        return $this->removal;
    }

    public function setRemoval(?Removal $removal): self
    {
        $this->removal = $removal;

        return $this;
    }
}
