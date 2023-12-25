<?php
// src/Entity/SecIpRule.php

namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Repository\SecIpRuleRepository;

#[ORM\Entity(repositoryClass: SecIpRuleRepository::class)]
#[ORM\Table(name: 'sonata_extra__sec_ip_rule')]
class SecIpRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $ip;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->ip;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }
}
