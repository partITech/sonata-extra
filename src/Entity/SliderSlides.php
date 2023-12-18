<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use Partitech\SonataExtra\Attribute\Translatable;
use Partitech\SonataExtra\Contract\MediaInterface;
use Partitech\SonataExtra\Repository\SliderRepository;

#[ORM\Entity(repositoryClass: SliderRepository::class)]
#[ORM\Table(name: 'sonata_extra__slider_slides', indexes: [
    new Index(name: 'fk_link_media__media1_idx', columns: ['media__media_id']),
])]
class SliderSlides
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    private ?string $subtitle = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    private ?string $btn_label = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $target = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $ordre = 0;

    #[ORM\ManyToOne(inversedBy: 'slides')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Slider $slider = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'media__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $mediaMedia;

    #[ORM\Column(nullable: true)]
    private ?bool $active = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $url = null;

    public function __construct()
    {
        $this->ordre = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getSlider(): ?Slider
    {
        return $this->slider;
    }

    public function setSlider(?Slider $slider): self
    {
        $this->slider = $slider;

        return $this;
    }

    public function getMediaMedia()
    {
        return $this->mediaMedia;
    }

    public function setMediaMedia($mediaMedia): self
    {
        $this->mediaMedia = $mediaMedia;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getBtnLabel(): ?string
    {
        return $this->btn_label;
    }

    public function setBtnLabel(?string $btn_label): void
    {
        $this->btn_label = $btn_label;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }
}
