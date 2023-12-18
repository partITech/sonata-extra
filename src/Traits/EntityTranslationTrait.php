<?php

namespace Partitech\SonataExtra\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Contract\SiteInterface;
use Partitech\SonataExtra\Enum\ArticleStatus;

trait EntityTranslationTrait
{
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $translation_from_id = null;

    #[ORM\ManyToOne(targetEntity: SiteInterface::class, inversedBy: 'site')]
    private $site;

    public ?array $translations = [];

    public function __construct()
    {
        $this->baseRouteName = [];
        
        // used in \Sonata\ClassificationBundle\Model\Category::__construct
        // cannot use parent::_construct as parent do not allways have constructor.
        $this->children = new ArrayCollection();
        
    }
    public function setId($id)
    {
        $this->id = $id;
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

    public function getSite()
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
