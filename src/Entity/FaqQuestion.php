<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Partitech\SonataExtra\Repository\FaqQuestionRepository;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: FaqQuestionRepository::class)]
#[ORM\Table(name: 'sonata_extra__faq_question')]
class FaqQuestion
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $question;

    #[ORM\Column(type: 'text')]
    private $answer;

    #[Gedmo\SortablePosition]
    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\ManyToOne(targetEntity: FaqCategory::class, inversedBy: 'questions')]
    private $category;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $active = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getQuestion();
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getCategory(): ?FaqCategory
    {
        return $this->category;
    }

    public function setCategory(?FaqCategory $category): self
    {
        $this->category = $category;

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
