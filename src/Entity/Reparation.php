<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"security"="is_granted('ROLE_ADMIN')"},
 *     normalizationContext={"groups"={"reparation"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ReparationRepository")
 * @ApiFilter(BooleanFilter::class, properties={"done"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "composter.slug" : "exact",
 *     "composter.name" : "partial"
 * })
 * @ApiFilter(OrderFilter::class, properties={"date"})
 */
class Reparation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"reparation"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"reparation"})
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"reparation"})
     */
    private $done;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"reparation"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"reparation"})
     */
    private $refFacture;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"reparation"})
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Composter", inversedBy="reparations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"reparation"})
     */
    private $composter;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nature;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\MediaObject")
     */
    private $gallery;

    public function __construct()
    {
        $this->gallery = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

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

    public function getRefFacture(): ?string
    {
        return $this->refFacture;
    }

    public function setRefFacture(?string $refFacture): self
    {
        $this->refFacture = $refFacture;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getComposter(): ?Composter
    {
        return $this->composter;
    }

    public function setComposter(?Composter $composter): self
    {
        $this->composter = $composter;

        return $this;
    }

    public function getNature(): ?string
    {
        return $this->nature;
    }

    public function setNature(?string $nature): self
    {
        $this->nature = $nature;

        return $this;
    }

    /**
     * @return Collection|MediaObject[]
     */
    public function getGallery(): Collection
    {
        return $this->gallery;
    }

    public function addGallery(MediaObject $gallery): self
    {
        if (!$this->gallery->contains($gallery)) {
            $this->gallery[] = $gallery;
        }

        return $this;
    }

    public function removeGallery(MediaObject $gallery): self
    {
        if ($this->gallery->contains($gallery)) {
            $this->gallery->removeElement($gallery);
        }

        return $this;
    }
}
