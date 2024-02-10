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
 *      attributes={"security"="is_granted('ROLE_ADMIN')"},
 *      normalizationContext={"groups"={"suivis"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\SuiviRepository")
 * @ApiFilter(SearchFilter::class, properties={
 *     "composter.name"     : "partial",
 *     "composter.slug"     : "exact",
 *     "composter.pole"     : "exact",
 *     "composter.commune"  : "exact"
 * })
 * @ApiFilter(OrderFilter::class, properties={"date"})
 */
class Suivi
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"suivis"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"suivis"})
     */
    private $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"suivis"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Composter", inversedBy="suivis")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"suivis"})
     */
    private $composter;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"suivis"})
     */
    private $animation;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"suivis"})
     */
    private $environnement;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"suivis"})
     */
    private $technique;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"suivis"})
     */
    private $autonomie;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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

    public function getAnimation(): ?int
    {
        return $this->animation;
    }

    public function setAnimation(?int $animation): self
    {
        $this->animation = $animation;

        return $this;
    }

    public function getEnvironnement(): ?int
    {
        return $this->environnement;
    }

    public function setEnvironnement(?int $environnement): self
    {
        $this->environnement = $environnement;

        return $this;
    }

    public function getTechnique(): ?int
    {
        return $this->technique;
    }

    public function setTechnique(?int $technique): self
    {
        $this->technique = $technique;

        return $this;
    }

    public function getAutonomie(): ?int
    {
        return $this->autonomie;
    }

    public function setAutonomie(?int $autonomie): self
    {
        $this->autonomie = $autonomie;

        return $this;
    }
}
