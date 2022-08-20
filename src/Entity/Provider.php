<?php

namespace App\Entity;

use App\Repository\ProviderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProviderRepository::class)]
class Provider
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    private $typeStruct;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $address;

    #[ORM\Column(type: 'string', length: 10)]
    private $zipCode;

    #[ORM\Column(type: 'string', length: 20)]
    private $attachment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $commercialContactName;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $commercialContactPhone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $commercialContactMail;

    #[ORM\Column(type: 'string', length: 255)]
    private $removalContactName;

    #[ORM\Column(type: 'string', length: 20)]
    private $removalContactPhone;

    #[ORM\Column(type: 'string', length: 255)]
    private $removalContactMail;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $certificateContactMail;

    #[ORM\Column(type: 'boolean')]
    private $isRegular;

    #[ORM\Column(type: 'text', nullable: true)]
    private $comment;

    #[ORM\OneToMany(mappedBy: 'provider', targetEntity: ContainerQuantity::class, orphanRemoval: true)]
    private $containersQuantitys;

    #[ORM\ManyToOne(targetEntity: CertificateRequestType::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $certificateRequestType;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $linkInfo;

    #[ORM\Column(type: 'string', length: 100)]
    private $city;

    public function __construct()
    {
        $this->containersQuantitys = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeStruct(): ?string
    {
        return $this->typeStruct;
    }

    public function setTypeStruct(string $typeStruct): self
    {
        $this->typeStruct = $typeStruct;

        return $this;
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

    public function getAttachment(): ?string
    {
        return $this->attachment;
    }

    public function setAttachment(string $attachment): self
    {
        $this->attachment = $attachment;

        return $this;
    }

    public function getCommercialContactName(): ?string
    {
        return $this->commercialContactName;
    }

    public function setCommercialContactName(string $commercialContactName): self
    {
        $this->commercialContactName = $commercialContactName;

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

    public function getCommercialContactMail(): ?string
    {
        return $this->commercialContactMail;
    }

    public function setCommercialContactMail(string $commercialContactMail): self
    {
        $this->commercialContactMail = $commercialContactMail;

        return $this;
    }

    public function getRemovalContactName(): ?string
    {
        return $this->removalContactName;
    }

    public function setRemovalContactName(string $removalContactName): self
    {
        $this->removalContactName = $removalContactName;

        return $this;
    }

    public function getRemovalContactPhone(): ?string
    {
        return $this->removalContactPhone;
    }

    public function setRemovalContactPhone(string $removalContactPhone): self
    {
        $this->removalContactPhone = $removalContactPhone;

        return $this;
    }

    public function getRemovalContactMail(): ?string
    {
        return $this->removalContactMail;
    }

    public function setRemovalContactMail(string $removalContactMail): self
    {
        $this->removalContactMail = $removalContactMail;

        return $this;
    }

    public function getCertificateContactMail(): ?string
    {
        return $this->certificateContactMail;
    }

    public function setCertificateContactMail(string $certificateContactMail): self
    {
        $this->certificateContactMail = $certificateContactMail;

        return $this;
    }

    public function getIsRegular(): ?bool
    {
        return $this->isRegular;
    }

    public function setIsRegular(bool $isRegular): self
    {
        $this->isRegular = $isRegular;

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

    /**
     * @return Collection<int, ContainerQuantity>
     */
    public function getContainersQuantitys(): Collection
    {
        return $this->containersQuantitys;
    }

    public function addContainersQuantity(ContainerQuantity $containersQuantity): self
    {
        if (!$this->containersQuantitys->contains($containersQuantity)) {
            $this->containersQuantitys[] = $containersQuantity;
            $containersQuantity->setProvider($this);
        }

        return $this;
    }

    public function removeContainersQuantity(ContainerQuantity $containersQuantity): self
    {
        if ($this->containersQuantitys->removeElement($containersQuantity)) {
            // set the owning side to null (unless already changed)
            if ($containersQuantity->getProvider() === $this) {
                $containersQuantity->setProvider(null);
            }
        }

        return $this;
    }

    public function getCertificateRequestType(): ?CertificateRequestType
    {
        return $this->certificateRequestType;
    }

    public function setCertificateRequestType(?CertificateRequestType $certificateRequestType): self
    {
        $this->certificateRequestType = $certificateRequestType;

        return $this;
    }

    public function getLinkInfo(): ?string
    {
        return $this->linkInfo;
    }

    public function setLinkInfo(?string $linkInfo): self
    {
        $this->linkInfo = $linkInfo;

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
}
