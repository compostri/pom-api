<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"commune"}}
 *  )
 * @ORM\Entity(repositoryClass="App\Repository\CommuneRepository")
 * @ApiFilter(OrderFilter::class, properties={"name"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(ExistsFilter::class, properties={"composters.lat", "composters.lng"})
 */
class Commune
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"composter", "commune"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"composter", "commune", "livraison"})
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Composter", mappedBy="commune")
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
            $composter->setCommune($this);
        }

        return $this;
    }

    public function removeComposter(Composter $composter): self
    {
        if ($this->composters->contains($composter)) {
            $this->composters->removeElement($composter);
            // set the owning side to null (unless already changed)
            if ($composter->getCommune() === $this) {
                $composter->setCommune(null);
            }
        }

        return $this;
    }
}
