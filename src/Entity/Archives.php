<?php

namespace App\Entity;

use App\Repository\ArchivesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArchivesRepository::class)]
#[ORM\Table(name: 'archives')]
#[ORM\Index(name: 'idx_archives_entity', columns: ['entity'])]
#[ORM\Index(name: 'idx_archives_entity_id', columns: ['entity_id'])]
#[ORM\Index(name: 'idx_archives_created_by', columns: ['created_by'])]
class Archives
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $entity = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $entity_id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    private ?string $action = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $data = null;

    #[ORM\ManyToOne(inversedBy: 'archives')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Users $createdBy = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    public function __construct() { $this->created_at = new \DateTime(); }
    public function __toString(): string { return $this->entity.' #'.$this->entity_id; }
    public function getId(): ?int { return $this->id; }
    public function getEntity(): ?string { return $this->entity; }
    public function setEntity(string $entity): static { $this->entity = $entity; return $this; }
    public function getEntityId(): ?int { return $this->entity_id; }
    public function setEntityId(int $entity_id): static { $this->entity_id = $entity_id; return $this; }
    public function getAction(): ?string { return $this->action; }
    public function setAction(string $action): static { $this->action = $action; return $this; }
    public function getData(): ?array { return $this->data; }
    public function setData(?array $data): static { $this->data = $data; return $this; }
    public function getCreatedBy(): ?Users { return $this->createdBy; }
    public function setCreatedBy(?Users $createdBy): static { $this->createdBy = $createdBy; return $this; }
    public function getCreatedAt(): ?\DateTime { return $this->created_at; }
    public function setCreatedAt(\DateTime $created_at): static { $this->created_at = $created_at; return $this; }
}
