<?php

namespace Partitech\SonataExtra\Traits;

use App\Entity\SonataPageSite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Contract\SiteInterface;

#[ORM\HasLifecycleCallbacks]
trait EntityTranslationTrait
{
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $translation_from_id = null;

    #[ORM\ManyToOne(targetEntity: SiteInterface::class, inversedBy: 'site')]
    private ?SonataPageSite $site=null;

    public ?array $translations = [];

    public function __construct()
    {
        $this->baseRouteName = [];
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }


    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTranslationFromId(): ?int
    {
        return $this->translation_from_id;
    }

    public function setTranslationFromId($translation_from_id): void
    {
        $this->translation_from_id = $translation_from_id;
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

    public function getSite(): ?SonataPageSite
    {
        return $this->site;
    }

    public function setSite($site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getBaseRouteName(): array
    {
        return !empty($this->baseRouteName)?$this->baseRouteName:[];
    }

    public function setBaseRouteName(array $baseRouteName): void
    {
        $this->baseRouteName = $baseRouteName;
    }
}
