<?php

namespace Partitech\SonataExtra\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Repository\ContactRepository;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'sonata-extra.block_contact.firstName_not_blank')]
    private ?string $firstName = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\NotBlank(message: 'sonata-extra.block_contact.lastName_not_blank')]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'sonata-extra.block_contact.companyName_not_blank')]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'sonata-extra.block_contact.address_not_blank')]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'sonata-extra.block_contact.email_not_blank')]
    #[Assert\Email(message: 'sonata-extra.block_contact.email_invalid')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'sonata-extra.block_contact.phone_not_blank')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{6,}$/',
        message: 'sonata-extra.block_contact.phone_invalid'
    )]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: 'sonata-extra.block_contact.additionalInformation_not_blank')]
    private ?string $additionalInformation = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $sendMeACopy = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAdditionalInformation(): ?string
    {
        return $this->additionalInformation;
    }

    public function setAdditionalInformation(?string $additionalInformation): static
    {
        $this->additionalInformation = $additionalInformation;

        return $this;
    }

    public function getSendMeACopy(): ?bool
    {
        return $this->sendMeACopy;
    }

    public function setSendMeACopy(?bool $sendMeACopy): Contact
    {
        $this->sendMeACopy = $sendMeACopy;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
