<?php
namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Repository\SecFirewallRuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: SecFirewallRuleRepository::class)]
#[ORM\Table(name: 'sonata_extra__sec_firewall_rule')]
class SecFirewallRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $label;

    #[ORM\Column(type: 'string', length: 255)]
    private $type; // 'stop_word', 'ip', 'user_agent', 'stop_word_db', 'ip_db'

    #[ORM\Column(type: 'string', length: 255)]
    private $source; //  'get', 'post', 'header'

    #[ORM\Column(type: 'json', nullable: true)]
    private $parameters = [];

    #[ORM\Column(type: 'string', length: 255)]
    private $matchMode;


    public function __toString(){
        return $this->getLabel();
    }

    public function __construct()
    {
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(string $rule): self
    {
        $this->rule = $rule;

        return $this;
    }



    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source): void
    {
        $this->source = $source;
    }

    public function getParameters(): array {
        return $this->parameters;
    }

    public function setParameters(array $parameters): self {
        $this->parameters = $parameters;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getMatchMode()
    {
        return $this->matchMode;
    }

    /**
     * @param mixed $matchMode
     */
    public function setMatchMode($matchMode): void
    {
        $this->matchMode = $matchMode;
    }
    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label): void
    {
        $this->label = $label;
    }
}
