<?php
namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Repository\SecFirewallRuleRepository;

#[ORM\Entity(repositoryClass: SecFirewallRuleRepository::class)]
#[ORM\Table(name: 'sonata_extra__sec_firewall_rule')]
class SecFirewallRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $label;

    #[ORM\Column(type: 'string', length: 255)]
    private string $type; // 'stop_word', 'ip', 'user_agent', 'stop_word_db', 'ip_db'

    #[ORM\Column(type: 'string', length: 255)]
    private string $source; //  'get', 'post', 'header'

    #[ORM\Column(type: 'json', nullable: true)]
    private array $parameters = [];

    #[ORM\Column(type: 'string', length: 255)]
    private string $matchMode;

    private string $rule;
    public function __toString(){
        return $this->getLabel();
    }

    public function __construct()
    {
    }

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
    public function getSource(): mixed
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource(mixed $source): void
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
     * @return string
     */
    public function getMatchMode(): string
    {
        return $this->matchMode;
    }

    /**
     * @param mixed $matchMode
     */
    public function setMatchMode(string $matchMode): void
    {
        $this->matchMode = $matchMode;
    }
    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }
}
