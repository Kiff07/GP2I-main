<?php

namespace App\Entity;

use App\Repository\SuppliersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuppliersRepository::class)]
#[ORM\Table(name: 'suppliers')]
class Suppliers
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $contact_name = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $phone = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, PurchaseOrders>
     */
    #[ORM\OneToMany(targetEntity: PurchaseOrders::class, mappedBy: 'supplier')]
    private Collection $purchaseOrders;

    /**
     * @var Collection<int, Materials>
     */
    #[ORM\OneToMany(targetEntity: Materials::class, mappedBy: 'supplier')]
    private Collection $materials;

    public function __construct()
    {
        $this->purchaseOrders = new ArrayCollection();
        $this->materials = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->name ?? 'Fournisseur';
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

    public function getContactName(): ?string
    {
        return $this->contact_name;
    }

    public function setContactName(?string $contact_name): static
    {
        $this->contact_name = $contact_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

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
     * @return Collection<int, PurchaseOrders>
     */
    public function getPurchaseOrders(): Collection
    {
        return $this->purchaseOrders;
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
            $material->setSupplier($this);
        }

        return $this;
    }

    public function removeMaterial(Materials $material): static
    {
        if ($this->materials->removeElement($material) && $material->getSupplier() === $this) {
            $material->setSupplier(null);
        }

        return $this;
    }
}
