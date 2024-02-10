<?php

/**
 * Created by PhpStorm.
 * User: arnaudbanvillet
 * Date: 12/10/2018
 * Time: 13:19.
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A Permanence.
 *
 * @ORM\Entity(repositoryClass="App\Repository\PermanenceRepository")
 *
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "post"={"security"="is_granted('Opener', object)"}
 *     },
 *     itemOperations={
 *         "get",
 *         "put"={"security"="is_granted('Opener', object)"},
 *         "delete"={"security"="is_granted('Referent', object)"}
 *     },
 *     normalizationContext={"groups"={"permanence"}}
 * )
 * @ApiFilter(OrderFilter::class, properties={"date":"ASC"})
 * @ApiFilter(DateFilter::class, properties={"date"})
 * @ApiFilter(SearchFilter::class, properties={"composter": "exact"})
 * @ApiFilter(ExistsFilter::class, properties={"nbUsers"})
 */
class Permanence
{
    /**
     * @var int The id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"permanence"})
     */
    private $id;

    /**
     * @var \DateTime Permanence date
     *
     * @ORM\Column(type="datetime")
     * @Groups({"permanence"})
     */
    public $date;

    /**
     * @var \boolean Permanence is canceled
     *
     * @ORM\Column(type="boolean", options={"default" : false})
     * @Groups({"permanence"})
     */
    public $canceled;

    /**
     * @var User[] People who will open the composter
     *
     * @ORM\ManyToMany(targetEntity="User", mappedBy="permanences")
     * @Groups({"permanence"})
     */
    public $openers;

    /**
     * @var string People without account who will open the composter
     *
     * @ORM\Column(type="string", nullable=true, options={"default" : null})
     * @Groups({"permanence"})
     */
    public $openersString;

    /**
     * @var Composter of the permanence
     *
     * @ORM\ManyToOne(targetEntity="Composter", inversedBy="permanences")
     * @Groups({"permanence"})
     */
    public $composter;

    /**
     * @var string Event title
     *
     * @ORM\Column(type="string", nullable=true, options={"default" : null})
     * @Groups({"permanence"})
     */
    public $eventTitle;

    /**
     * @var string Event Message
     *
     * @ORM\Column(type="text", nullable=true, options={"default" : null})
     * @Groups({"permanence"})
     */
    public $eventMessage;

    /**
     * @var int Number of persons who show up
     *
     * @ORM\Column(type="smallint", nullable=true, options={"default" : null})
     * @Groups({"permanence"})
     */
    public $nbUsers;

    /**
     * @var float Number of buckets added to the composter
     *
     * @ORM\Column(type="float", nullable=true, options={"default" : null})
     * @Groups({"permanence"})
     */
    public $nbBuckets;

    /**
     * @var float composter temperature
     *
     * @ORM\Column(type="float", nullable=true, options={"default" : null})
     * @Groups({"permanence"})
     */
    public $temperature;

    /**
     * @var float Poid total de biodéchet détourné
     *
     * @ORM\Column(type="float", nullable=true, options={"default" : null})
     * @Groups({"permanence"})
     */
    public $weight;

    /**
     * @var bool has users been notify
     *
     * @ORM\Column(type="boolean", options={"default" : false})
     */
    public $hasUsersBeenNotify;

    public function __construct()
    {
        $this->openers = new ArrayCollection();
        $this->canceled = false;
        $this->hasUsersBeenNotify = false;
    }

    public function __toString()
    {
        return $this->getDate() ? $this->getDate()->format('Y-m-d H:i:s') : (string) $this->getId();
    }

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

    /**
     * @return Collection|User[]
     */
    public function getOpeners(): Collection
    {
        return $this->openers;
    }

    public function addOpener(User $opener): self
    {
        if (!$this->openers->contains($opener)) {
            $this->openers[] = $opener;
            $opener->addPermanence($this);
        }

        return $this;
    }

    public function removeOpener(User $opener): self
    {
        if ($this->openers->contains($opener)) {
            $this->openers->removeElement($opener);
            $opener->removePermanence($this);
        }

        return $this;
    }

    public function getCanceled(): ?bool
    {
        return $this->canceled;
    }

    public function setCanceled(bool $canceled): self
    {
        $this->canceled = $canceled;

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

    public function getNbUsers()
    {
        return $this->nbUsers;
    }

    public function setNbUsers(?int $nbUsers): self
    {
        $this->nbUsers = $nbUsers;

        return $this;
    }

    public function getNbBuckets(): ?float
    {
        return $this->nbBuckets;
    }

    public function setNbBuckets(?float $nbBuckets): self
    {
        $this->nbBuckets = $nbBuckets;

        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(?float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getEventTitle(): ?string
    {
        return $this->eventTitle;
    }

    public function setEventTitle(?string $eventTitle): self
    {
        $this->eventTitle = $eventTitle;

        return $this;
    }

    public function getEventMessage(): ?string
    {
        return $this->eventMessage;
    }

    public function setEventMessage(?string $eventMessage): self
    {
        $this->eventMessage = $eventMessage;

        return $this;
    }

    public function getHasUsersBeenNotify(): ?bool
    {
        return $this->hasUsersBeenNotify;
    }

    public function setHasUsersBeenNotify(bool $hasUsersBeenNotify): self
    {
        $this->hasUsersBeenNotify = $hasUsersBeenNotify;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getOpenersString(): ?string
    {
        return $this->openersString;
    }

    public function setOpenersString(?string $openersString): self
    {
        $this->openersString = $openersString;

        return $this;
    }
}
