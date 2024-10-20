<?php

namespace Partitech\SonataExtra\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: 'Partitech\SonataExtra\Repository\AdminActivityEntityChangeLogRepository')]
#[ORM\Table(name: 'sonata_extra__admin_activity_change_log')]
class AdminActivityEntityChangeLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $entityClass;

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $entityId;

    #[ORM\Column(type: 'string', length: 255)]
    private string $fieldName;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $oldValue;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $newValue;

    #[ORM\ManyToOne(targetEntity: AdminActivityLog::class, inversedBy: 'entityChangeLogs')]
    private ?AdminActivityLog $adminActivityLog;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): self
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    public function setOldValue(?string $oldValue): self
    {
        $this->oldValue = $oldValue;

        return $this;
    }

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    public function setNewValue(?string $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }

    public function getAdminActivityLog(): ?AdminActivityLog
    {
        return $this->adminActivityLog;
    }

    public function setAdminActivityLog(?AdminActivityLog $adminActivityLog): self
    {
        $this->adminActivityLog = $adminActivityLog;

        return $this;
    }
}
