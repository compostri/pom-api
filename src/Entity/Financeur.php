<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"security"="is_granted('ROLE_ADMIN')"},
 * )
 * @ORM\Entity(repositoryClass="App\Repository\FinanceurRepository")
 * @ApiFilter(OrderFilter::class, properties={"name"})
 */
class Financeur
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"composter"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"composter"})
     */
    private $initials;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Composter", mappedBy="financeur")
     */
    private $composters;

    public function __construct()
    {
        $this->composters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getInitials(): ?string
    {
        return $this->initials;
    }

    public function setInitials(string $initials): self
    {
        $this->initials = $initials;

        return $this;
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
            $composter->setFinanceur($this);
        }

        return $this;
    }

    public function removeComposter(Composter $composter): self
    {
        if ($this->composters->contains($composter)) {
            $this->composters->removeElement($composter);
            // set the owning side to null (unless already changed)
            if ($composter->getFinanceur() === $this) {
                $composter->setFinanceur(null);
            }
        }

        return $this;
    }
}
