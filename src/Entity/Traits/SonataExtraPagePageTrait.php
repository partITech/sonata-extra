<?php

namespace Partitech\SonataExtra\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Contract\MediaInterface;

trait SonataExtraPagePageTrait
{
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $translation_from_id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $ogTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $ogDescription = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'], fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'media__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaInterface $ogImage=null;

    public ?array $translations=null;


    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    public function setOgTitle(?string $ogTitle): self
    {
        $this->ogTitle = $ogTitle;

        return $this;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    public function setOgDescription(?string $ogDescription): self
    {
        $this->ogDescription = $ogDescription;

        return $this;
    }

    public function getOgImage(): ?MediaInterface
    {
        return $this->ogImage;
    }

    public function setOgImage(?MediaInterface $ogImage): self
    {
        $this->ogImage = $ogImage;

        return $this;
    }

    public function getTranslationFromId(): ?int
    {
        return $this->translation_from_id;
    }

    public function setTranslationFromId(int $translation_from_id): self
    {
        $this->translation_from_id = $translation_from_id;

        return $this;
    }

    public function setTranslations(array $locales): self
    {
        $this->translations = $locales;

        return $this;
    }

    public function getTranslations(): ?array
    {
        return $this->translations;
    }

    public function getBaseRouteName(): array
    {
        return !empty($this->baseRouteName) ? $this->baseRouteName : [];
    }

    public function setBaseRouteName(array $baseRouteName): self
    {
        $this->baseRouteName = $baseRouteName;

        return $this;
    }
}
