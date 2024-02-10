<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     attributes={"security"="is_granted('ROLE_ADMIN')"},
 *     normalizationContext={"groups"={"livraison"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\LivraisonBroyatRepository")
 * @ApiFilter(OrderFilter::class, properties={"date"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "composter.slug"     : "exact",
 *     "composter.name"     : "partial"
 * })
 * @ORM\EntityListeners({"App\EventListener\LivraisonBroyatListener"})
 */
class LivraisonBroyat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"livraison"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"livraison"})
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"livraison"})
     */
    private $quantite;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Composter", inversedBy="livraisonBroyats")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"livraison"})
     */
    private $composter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ApprovisionnementBroyat", inversedBy="livraisonBroyats")
     * @Groups({"livraison"})
     */
    private $livreur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

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

    public function getLivreur(): ?ApprovisionnementBroyat
    {
        return $this->livreur;
    }

    public function setLivreur(?ApprovisionnementBroyat $livreur): self
    {
        $this->livreur = $livreur;

        return $this;
    }
}
