<?php

namespace App\Entity;

use App\Repository\CategoriesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
#[ORM\Table(name: 'categories')]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Materials>
     */
    #[ORM\OneToMany(targetEntity: Materials::class, mappedBy: 'category')]
    private Collection $materials;

    public function __construct()
    {
        $this->materials = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? 'Categorie';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Materials>
     */
    public function getMaterials(): Collection
    {
        return $this->materials;
    }

    public function addMaterial(Materials $material): static
    {
        if (!$this->materials->contains($material)) {
            $this->materials->add($material);
            $material->setCategory($this);
        }

        return $this;
    }

    public function removeMaterial(Materials $material): static
    {
        if ($this->materials->removeElement($material) && $material->getCategory() === $this) {
            $material->setCategory(null);
        }

        return $this;
    }
}
