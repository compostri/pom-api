<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"security"="is_granted('ROLE_ADMIN')"},
 *     normalizationContext={"groups"={"equipement"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\EquipementRepository")
 * @ORM\Table(uniqueConstraints={@UniqueConstraint(name="equipement", columns={"type", "capacite"})})
 */
class Equipement
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"composter", "equipement"})
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Composter", mappedBy="pavilionsVolume")
     */
    private $composters;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"composter", "equipement"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"composter", "equipement"})
     */
    private $capacite;

    public function __construct()
    {
        $this->composters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Composter[]
     */
    public function getComposters(): Collection
    {
        return $this->composters;
    }

    public function addComposter(Composter $composter): self
    {
        if (!$this->composters->contains($composter)) {
            $this->composters[] = $composter;
            $composter->setPavilionsVolume($this);
        }

        return $this;
    }

    public function removeComposter(Composter $composter): self
    {
        if ($this->composters->contains($composter)) {
            $this->composters->removeElement($composter);
            // set the owning side to null (unless already changed)
            if ($composter->getPavilionsVolume() === $this) {
                $composter->setPavilionsVolume(null);
            }
        }

        return $this;
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

    public function getCapacite(): ?string
    {
        return $this->capacite;
    }

    public function setCapacite(string $capacite): self
    {
        $this->capacite = $capacite;

        return $this;
    }
}
