<?php

namespace Partitech\SonataExtra\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use Partitech\SonataExtra\Contract\MediaInterface;
use Partitech\SonataExtra\Contract\UserInterface;
use Partitech\SonataExtra\Enum\ArticleStatus;
use Partitech\SonataExtra\Markdown\VideoLinkExtension;
use Partitech\SonataExtra\Traits\EntityTranslationTrait;
use Partitech\SonataExtra\Attribute\Translatable;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: 'Partitech\SonataExtra\Repository\EditorRepository')]
#[ORM\Table(name: 'sonata_extra__editor')]
class Editor
{
    use EntityTranslationTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Translatable]
    private string $content;

    #[ORM\Column(type: 'string', length: 255)]
    #[Translatable]
    private string $title;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?DateTime $publishedAt = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: true)]
    private UserInterface $author;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => 'gutenberg'])]
    private string $type_editor = 'textarea';

    #[ORM\OneToMany(targetEntity: 'EditorRevision', mappedBy: 'editor', cascade: ['persist', 'remove'])]
    #[Serializer\Groups(['default'])]
    #[Serializer\MaxDepth(1)]
    private Collection $revisions;

    #[ORM\ManyToOne(targetEntity: MediaInterface::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'featured_image__media_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?MediaInterface $featured_image=null;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
        $this->status = ArticleStatus::DRAFT->value;
        $this->title='';

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

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @throws CommonMarkException
     */
    public function getHtmlContent(): string
    {

        if($this->getTypeEditor()=='markdown'){

            $config = [];
            $environment = new Environment($config);
            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new VideoLinkExtension());

            $converter = new MarkdownConverter($environment);

            $htmlContent =  $converter->convert($this->content);


            return '<div class="markdown-body">'.$htmlContent.'</div>';

        }else{
            return $this->content;
        }

    }
    public function getRevisions(): Collection
    {
        return $this->revisions;
    }

    public function addRevision(EditorRevision $revision): self
    {
        if (!$this->revisions->contains($revision)) {
            $this->revisions->add($revision);
            $revision->setEditor($this);
        }

        return $this;
    }

    public function removeRevision(EditorRevision $revision): self
    {
        if ($this->revisions->contains($revision)) {
            $this->revisions->removeElement($revision);
            // set the owning side to null (unless already changed)
            if ($revision->getEditor() === $this) {
                $revision->setEditor(null);
            }
        }

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

        if ($status === ArticleStatus::PUBLISHED->value && null === $this->publishedAt) {
            $this->setPublishedAt(new DateTime());
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

    public function getFeaturedImage(): ?MediaInterface
    {
        return $this->featured_image;
    }

    public function setFeaturedImage($featured_image): self
    {
        $this->featured_image = $featured_image;

        return $this;
    }
}
