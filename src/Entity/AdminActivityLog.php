<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Partitech\SonataExtra\Contract\UserInterface;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Index(columns: ['token'], name: 'token_idx')]
#[ORM\Entity(repositoryClass: 'Partitech\SonataExtra\Repository\AdminActivityLogRepository')]
#[ORM\Table(name: 'sonata_extra__admin_activity_log')]
class AdminActivityLog
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $date;

    #[ORM\Column(type: 'string', length: 255)]
    private string $actionType;  // Par exemple: "create", "update", "delete"

    #[ORM\Column(type: 'string', length: 255)]
    private string $resource;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $data;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: true)]
    private $user;  // User entity relation, adjust accordingly

    #[ORM\OneToMany(targetEntity: AdminActivityEntityChangeLog::class, mappedBy: 'adminActivityLog')]
    #[Serializer\Groups(['default'])]
    #[Serializer\MaxDepth(1)]
    private Collection $entityChangeLogs;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private bool $approval;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $token;

    public function __construct()
    {
        $this->entityChangeLogs = new ArrayCollection();
        $this->approval = 0;
    }

    public function __toString(): string
    {
        return $this->getDescription();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getActionType(): ?string
    {
        return $this->actionType;
    }

    public function setActionType(string $actionType): self
    {
        $this->actionType = $actionType;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|AdminActivityEntityChangeLog[]
     */
    public function getEntityChangeLogs(): Collection
    {
        return $this->entityChangeLogs;
    }

    public function addEntityChangeLog(AdminActivityEntityChangeLog $entityChangeLog): self
    {
        if (!$this->entityChangeLogs->contains($entityChangeLog)) {
            $this->entityChangeLogs[] = $entityChangeLog;
            $entityChangeLog->setAdminActivityLog($this);
        }

        return $this;
    }

    public function removeEntityChangeLog(AdminActivityEntityChangeLog $entityChangeLog): self
    {
        if ($this->entityChangeLogs->removeElement($entityChangeLog)) {
            // set the owning side to null (unless already changed)
            if ($entityChangeLog->getAdminActivityLog() === $this) {
                $entityChangeLog->setAdminActivityLog(null);
            }
        }

        return $this;
    }

    public function isApproved(): int
    {
        return $this->approval;
    }

    public function setApproval(int $approval): self
    {
        $this->approval = $approval;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
