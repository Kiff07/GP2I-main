<?php

namespace App\Entity;

use App\Repository\AssetImagesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssetImagesRepository::class)]
#[ORM\Table(name: 'asset_images')]
#[ORM\Index(name: 'idx_asset_images_material_id', columns: ['material_id'])]
class AssetImages
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'assetImages')]
    #[ORM\JoinColumn(name: 'material_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Materials $material = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $caption = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    public function __construct() { $this->created_at = new \DateTime(); }
    public function __toString(): string { return $this->caption ?: ($this->filename ?? 'Image'); }
    public function getId(): ?int { return $this->id; }
    public function getMaterial(): ?Materials { return $this->material; }
    public function setMaterial(?Materials $material): static { $this->material = $material; return $this; }
    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): static { $this->filename = $filename; return $this; }
    public function getCaption(): ?string { return $this->caption; }
    public function setCaption(?string $caption): static { $this->caption = $caption; return $this; }
    public function getCreatedAt(): ?\DateTime { return $this->created_at; }
    public function setCreatedAt(\DateTime $created_at): static { $this->created_at = $created_at; return $this; }
}
