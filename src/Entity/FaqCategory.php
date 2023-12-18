<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Partitech\SonataExtra\Repository\FaqCategoryRepository;

#[ORM\Entity(repositoryClass: FaqCategoryRepository::class)]
#[ORM\Table(name: 'sonata_extra__faq_category')]
class FaqCategory
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(targetEntity: 'Partitech\SonataExtra\Entity\FaqQuestion', mappedBy: 'category')]
    private $questions;

    #[Gedmo\SortablePosition]
    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $active = false;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection|FaqQuestion[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
