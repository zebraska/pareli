<?php

namespace App\Entity;

use App\Repository\RecyclerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecyclerRepository::class)]
class Recycler
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $address;

    #[ORM\Column(type: 'string', length: 10)]
    private $zipCode;

    #[ORM\Column(type: 'string', length: 100)]
    private $city;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $commercialContactName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $commercialContactMail;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $commercialContactPhone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contactName;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $contactTelOne;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $contactTelTwo;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $contactMail;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCommercialContactName(): ?string
    {
        return $this->commercialContactName;
    }

    public function setCommercialContactName(?string $commercialContactName): self
    {
        $this->commercialContactName = $commercialContactName;

        return $this;
    }

    public function getCommercialContactMail(): ?string
    {
        return $this->commercialContactMail;
    }

    public function setCommercialContactMail(string $commercialContactMail): self
    {
        $this->commercialContactMail = $commercialContactMail;

        return $this;
    }

    public function getCommercialContactPhone(): ?string
    {
        return $this->commercialContactPhone;
    }

    public function setCommercialContactPhone(string $commercialContactPhone): self
    {
        $this->commercialContactPhone = $commercialContactPhone;

        return $this;
    }

    public function getContactName(): ?string
    {
        return $this->contactName;
    }

    public function setContactName(string $contactName): self
    {
        $this->contactName = $contactName;

        return $this;
    }

    public function getContactTelOne(): ?string
    {
        return $this->contactTelOne;
    }

    public function setContactTelOne(string $contactTelOne): self
    {
        $this->contactTelOne = $contactTelOne;

        return $this;
    }

    public function getContactTelTwo(): ?string
    {
        return $this->contactTelTwo;
    }

    public function setContactTelTwo(?string $contactTelTwo): self
    {
        $this->contactTelTwo = $contactTelTwo;

        return $this;
    }

    public function getContactMail(): ?string
    {
        return $this->contactMail;
    }

    public function setContactMail(string $contactMail): self
    {
        $this->contactMail = $contactMail;

        return $this;
    }
    
    public function getDisplayAddress(): string
    {
        return $this->address."\n".$this->city.' '.$this->zipCode;
    }
}
