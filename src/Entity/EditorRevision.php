<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Contract\MediaInterface;
use Partitech\SonataExtra\Contract\UserInterface;
use Partitech\SonataExtra\Enum\ArticleStatus;

#[ORM\Entity(repositoryClass: 'Partitech\SonataExtra\Repository\EditorRevisionRepository')]
#[ORM\Table(name: 'sonata_extra__editor_revision')]
class EditorRevision
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $author;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'gutenberg'])]
    private string $type_editor = 'gutenberg';

    #[ORM\ManyToOne(targetEntity: 'Partitech\SonataExtra\Entity\Editor', inversedBy: 'revisions')]
    private ?Editor $editor = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $revisionDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $publishedAt = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'featured_image__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $featured_image;

    public function __construct()
    {
        $this->status = ArticleStatus::DRAFT->value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->getId() ?: 'New Revision';
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getEditor(): ?Editor
    {
        return $this->editor;
    }

    public function setEditor(?Editor $editor): self
    {
        $this->editor = $editor;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getRevisionDate(): ?\DateTimeInterface
    {
        return $this->revisionDate;
    }

    public function setRevisionDate(?\DateTimeInterface $revisionDate): self
    {
        $this->revisionDate = $revisionDate;

        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getStatus(): string  // nouveau getter
    {
        return $this->status;
    }

    public function setStatus(string $status): self  // nouveau setter
    {
        $this->status = $status;

        return $this;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getFeaturedImage()
    {
        return $this->featured_image;
    }

    public function setFeaturedImage($featured_image): self
    {
        $this->featured_image = $featured_image;

        return $this;
    }

    public function getTypeEditor(): string
    {
        return $this->type_editor;
    }

    public function setTypeEditor(string $type_editor): void
    {
        $this->type_editor = $type_editor;
    }
}
