<?php
namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Repository\SecStopWordRepository;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: SecStopWordRepository::class)]
#[ORM\Table(name: 'sonata_extra__sec_stop_word')]
class SecStopWord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $word;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->getWord();
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(string $word): self
    {
        $this->word = $word;

        return $this;
    }
}
