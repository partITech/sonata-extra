<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Attribute\Translatable;
use Partitech\SonataExtra\Repository\SliderRepository;
use Partitech\SonataExtra\Traits\EntityTranslationTrait;

#[ORM\Entity(repositoryClass: SliderRepository::class)]
#[ORM\Table(name: 'sonata_extra__slider')]
class Slider
{
    use EntityTranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Translatable]
    private ?string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'slider', targetEntity: SliderSlides::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private Collection $slides;

    public function __construct()
    {
        $this->slides = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSlides(): ?Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['ordre' => Criteria::ASC]);

        return $this->slides->matching($criteria);
    }

    public function getActiveSlides(): ?Collection
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('active', true))
            ->orderBy(['ordre' => Criteria::ASC]);

        return $this->slides->matching($criteria);
    }

    public function addSlide(SliderSlides $slide): self
    {
        if (!$this->slides->contains($slide)) {
            $this->slides[] = $slide;
            $slide->setSlider($this);
        }

        return $this;
    }

    public function removeSlide(SliderSlides $slide): self
    {
        if ($this->slides->removeElement($slide)) {
            // set the owning side to null (unless already changed)
            if ($slide->getSlider() === $this) {
                $slide->setSlider(null);
            }
        }

        return $this;
    }
}
