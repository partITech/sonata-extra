<?php

namespace Partitech\SonataExtra\Entity;

use App\Entity\SonataMediaMedia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use Partitech\SonataExtra\Contract\CategoryInterface;
use Partitech\SonataExtra\Contract\MediaInterface;
use Partitech\SonataExtra\Contract\TagInterface;
use Partitech\SonataExtra\Contract\UserInterface;
use Partitech\SonataExtra\Enum\ArticleStatus;
use Partitech\SonataExtra\Traits\EntityTranslationTrait;
use Partitech\SonataExtra\Attribute\Translatable;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Index(columns: ['content'], name: 'content_fulltext_idx', flags: ['fulltext'])]
#[ORM\Entity(repositoryClass: 'Partitech\SonataExtra\Repository\ArticleRepository')]
#[ORM\Table(name: 'sonata_extra__article')]
class Article
{
    use EntityTranslationTrait;

    private array $baseRouteName=[
        'sonata_extra_blog_article',
        'sonata_extra_blog_search'
    ];

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: "is_default", type: "boolean")]
    private bool $isDefault = false;



    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    private ?string $content = "";

    #[ORM\Column(type: 'string', length: 255)]
    #[Translatable]
    private string $title;

    #[ORM\Column(type: 'text',  nullable: true)]
    #[Translatable]
    private ?string $excerpt;

    #[Gedmo\Slug(fields: ['title'], updatable: false, unique: true)]
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Translatable]
    private string $slug;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $publishedAt = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $author;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'markdown'])]
    private string $type_editor = 'markdown';

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: ArticleRevision::class, cascade: ['persist', 'remove'])]
    #[Serializer\Groups(['default'])]
    #[Serializer\MaxDepth(1)]
    private Collection $revisions;

    #[ORM\ManyToMany(targetEntity: CategoryInterface::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinTable(name: 'many_to_many__article_category')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Serializer\Groups(['default'])]
    #[Serializer\MaxDepth(1)]
    private Collection $category;

    #[ORM\ManyToMany(targetEntity: TagInterface::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinTable(name: 'many_to_many__article_tag')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Serializer\Groups(['default'])]
    #[Serializer\MaxDepth(1)]
    private Collection $tags;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'featured_image__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $featured_image;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    private ?string $seo_title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    private ?string $seo_keywords = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    private ?string $seo_description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Translatable]
    private ?string $seo_og_title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Translatable]
    private ?string $seo_og_description = null;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'seo_og_image__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $seo_og_image;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
        $this->status = ArticleStatus::DRAFT->value;
        $this->tags = new ArrayCollection();
        $this->category = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getHtmlContent(): string
    {
        if($this->getTypeEditor()=='markdown'){

            $config = [];
            $environment = new Environment($config);
            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new \Partitech\SonataExtra\Markdown\VideoLinkExtension());

            $converter = new MarkdownConverter($environment);

            $htmlContent =  $converter->convert($this->content);


            $dom = new \DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();
            $h1s = $dom->getElementsByTagName('h1');
            for ($i = $h1s->length - 1; $i >= 0; $i--) {
                $h1 = $h1s->item($i);
                $h1->parentNode->removeChild($h1);
            }
            $newHtmlContent = $dom->saveHTML();
            return '<div class="markdown-body">'.$newHtmlContent.'</div>';

        }else{
            return $this->content;
        }

    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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

    public function setStatus(string $status): self
    {
        $this->status = $status;

        if ($status === ArticleStatus::PUBLISHED->value && null === $this->publishedAt) {
            $this->setPublishedAt(new \DateTime());
        }

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

    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(ArticleRevision $revision): self
    {
        if (!$this->revisions->contains($revision)) {
            $this->revisions->add($revision);
            $revision->setArticle($this);
        }

        return $this;
    }

    public function removeRevision(ArticleRevision $revision): self
    {
        if ($this->revisions->contains($revision)) {
            $this->revisions->removeElement($revision);
            // set the owning side to null (unless already changed)
            if ($revision->getArticle() === $this) {
                $revision->setArticle(null);
            }
        }

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

    public function removeCatgory($category): self
    {
        $this->category->removeElement($category);

        return $this;
    }

    public function getFeaturedImage()
    {
        return $this->featured_image;
    }

    public function setFeaturedImage(?SonataMediaMedia $featured_image): self
    {
        $this->featured_image = $featured_image;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt;
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

    public function getSeoOgImage()
    {
        return $this->seo_og_image;
    }

    public function setSeoOgImage($seo_og_image): self
    {
        $this->seo_og_image = $seo_og_image;

        return $this;
    }

    public function generateExcerpt($maxChars = 500)
    {
        $content = $this->getHtmlContent();

        $contentWithoutTags = strip_tags($content);

        if (mb_strlen($contentWithoutTags) <= $maxChars) {
            return $contentWithoutTags;
        } else {
            $excerpt = mb_substr($contentWithoutTags, 0, $maxChars - 3) . '...';
            return $excerpt;
        }
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getBaseRouteName(): array
    {
        return $this->baseRouteName;
    }

    public function setBaseRouteName(array $baseRouteName): void
    {
        $this->baseRouteName = $baseRouteName;
    }

}
