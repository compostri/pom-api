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
 *     normalizationContext={"groups"={"categorie"}}
 *  )
 * @ORM\Entity(repositoryClass="App\Repository\CategorieRepository")
 * @ApiFilter(OrderFilter::class, properties={"name"})
 */
class Categorie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"composter", "categorie"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"composter", "categorie"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Composter", mappedBy="categorie")
     */
    private $composteurs;

    public function __construct()
    {
        $this->composteurs = new ArrayCollection();
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

    /**
     * @return Collection|Composter[]
     */
    public function getComposteurs(): Collection
    {
        return $this->composteurs;
    }

    public function addComposteur(Composter $composteur): self
    {
        if (!$this->composteurs->contains($composteur)) {
            $this->composteurs[] = $composteur;
            $composteur->setCategorie($this);
        }

        return $this;
    }

    public function removeComposteur(Composter $composteur): self
    {
        if ($this->composteurs->contains($composteur)) {
            $this->composteurs->removeElement($composteur);
            // set the owning side to null (unless already changed)
            if ($composteur->getCategorie() === $this) {
                $composteur->setCategorie(null);
            }
        }

        return $this;
    }
}
