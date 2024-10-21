<?php

namespace Partitech\SonataExtra\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Contract\CategoryInterface;
use Partitech\SonataExtra\Contract\MediaInterface;
use Partitech\SonataExtra\Contract\TagInterface;
use Partitech\SonataExtra\Contract\UserInterface;
use Partitech\SonataExtra\Enum\ArticleStatus;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: 'Partitech\SonataExtra\Repository\ArticleRevisionRepository')]
#[ORM\Table(name: 'sonata_extra__article_revision')]
class ArticleRevision
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $slug;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: true)]
    private UserInterface $author;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'gutenberg'])]
    private string $type_editor = 'gutenberg';

    #[ORM\ManyToOne(targetEntity: 'Partitech\SonataExtra\Entity\Article', inversedBy: 'revisions')]
    private ?Article $article = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $revisionDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $publishedAt = null;

    #[ORM\ManyToMany(targetEntity: CategoryInterface::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinTable(name: 'many_to_many__article_category_revision')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Serializer\Groups(['default'])]
    #[Serializer\MaxDepth(1)]
    private Collection $category;

    #[ORM\ManyToMany(targetEntity: TagInterface::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinTable(name: 'many_to_many__article_tag_revision')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Serializer\Groups(['default'])]
    #[Serializer\MaxDepth(1)]
    private Collection $tags;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'featured_image__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaInterface $featured_image=null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_keywords = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $seo_description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $seo_og_title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $seo_og_description = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'seo_og_image__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaInterface $seo_og_image=null;

    public function __construct()
    {
        $this->status = ArticleStatus::DRAFT->value;
        $this->tags = new ArrayCollection();
        $this->category = new ArrayCollection();
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

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

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

    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    public function setAuthor($author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag($tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag($tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory($category): self
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory($category): self
    {
        $this->category->removeElement($category);

        return $this;
    }

    public function getFeaturedImage(): ?MediaInterface
    {
        return $this->featured_image;
    }

    public function setFeaturedImage(?MediaInterface $featured_image): self
    {
        $this->featured_image = $featured_image;

        return $this;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seo_title;
    }

    public function setSeoTitle(?string $seo_title): self
    {
        $this->seo_title = $seo_title;

        return $this;
    }

    public function getSeoKeywords(): ?string
    {
        return $this->seo_keywords;
    }

    public function setSeoKeywords(?string $seo_keywords): self
    {
        $this->seo_keywords = $seo_keywords;

        return $this;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function setSeoDescription(?string $seo_description): self
    {
        $this->seo_description = $seo_description;

        return $this;
    }

    public function getSeoOgTitle(): ?string
    {
        return $this->seo_og_title;
    }

    public function setSeoOgTitle(?string $seo_og_title): self
    {
        $this->seo_og_title = $seo_og_title;

        return $this;
    }

    public function getSeoOgDescription(): ?string
    {
        return $this->seo_og_description;
    }

    public function setSeoOgDescription(?string $seo_og_description): self
    {
        $this->seo_og_description = $seo_og_description;

        return $this;
    }

    public function getSeoOgImage(): ?MediaInterface
    {
        return $this->seo_og_image;
    }

    public function setSeoOgImage(?MediaInterface $seo_og_image): self
    {
        $this->seo_og_image = $seo_og_image;

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
