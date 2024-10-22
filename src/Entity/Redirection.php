<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Repository\RedirectionRepository;

#[ORM\Entity(repositoryClass: RedirectionRepository::class)]
#[ORM\Table(name: 'sonata_extra__redirection')]
class Redirection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled;

    #[ORM\Column(type: 'string', length: 255)]
    private string $source;

    #[ORM\Column(type: 'string', length: 255)]
    private string $sourceHost;

    #[ORM\Column(type: 'string', length: 255)]
    private string $target;

    #[ORM\Column(type: 'integer')]
    private int $statusCode;

    #[ORM\Column(type: 'string', length: 255)]
    private string $user;

    public function __toString(): string
    {
        return $this->getId().' : '.$this->getSource().' => '.$this->getStatusCodeLabel();
    }

    public function getStatusCodeLabel(): ?string
    {
        $labels = [
            301 => '301 Moved Permanently',
            302 => '302 Found',
            303 => '303 See Other',
            307 => '307 Temporary Redirect',
            308 => '308 Permanent Redirect',
        ];

        return $labels[$this->statusCode] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSourceHost(): ?string
    {
        return $this->sourceHost;
    }

    public function setSourceHost(string $sourceHost): self
    {
        $this->sourceHost = $sourceHost;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }
}
