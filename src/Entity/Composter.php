<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\DBAL\Types\CapabilityEnumType;
use App\Filter\YearFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Composter. Lieux ou l'on transform les bio déchets en composte.
 *
 * @ORM\Entity(repositoryClass="App\Repository\ComposterRepository")
 * @ApiResource(
 *     normalizationContext={"groups"={"composter"}},
 *     denormalizationContext={"groups"={"composter", "composter:write"}}
 * )
 * @ApiFilter(BooleanFilter::class, properties={"acceptNewMembers"})
 * @ApiFilter(YearFilter::class, properties={"DateMiseEnRoute"})
 * @ApiFilter(SearchFilter::class, properties={
 *     "commune"        : "exact",
 *     "quartier"       : "exact",
 *     "pole"           : "exact",
 *     "equipement"     : "exact",
 *     "categorie"      : "exact",
 *     "financeur"      : "exact",
 *     "financeurSuivi" : "exact",
 *     "contacts"       : "exact",
 *     "status"         : "exact",
 *     "name"           : "partial",
 *     "serialNumber"   : "partial",
 *     "broyatLevel"    : "exact"
 * })
 * @ORM\EntityListeners({"App\EventListener\ComposterListener"})
 * @ApiFilter(OrderFilter::class, properties={"DateMiseEnRoute", "status", "name", "serialNumber"}, arguments={"orderParameterName"="order"})
 */
class Composter
{
    /**
     * @var int The id of the composter
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=false)
     */
    private $id;

    /**
     * @var int The id of the composter for the backoffice
     * @Groups({"composter", "suivis", "livraison"})
     */
    private $rid;

    /**
     * @var string The name of the composter
     *
     * @ORM\Column
     * @Groups({"composter", "suivis", "livraison", "reparation", "permanence","userComposter", "contact", "user:read", "media_object_read"})
     */
    private $name;

    /**
     * @var string The slug of the composter
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Gedmo\Slug(fields={"name"})
     * @Groups({"composter", "suivis", "livraison", "reparation", "permanence", "userComposter",})
     * @ApiProperty(identifier=true)
     */
    private $slug;

    /**
     * @var string The short description of the composter to be shown on the composter page
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"composter"})
     */
    private $permanencesDescription;

    /**
     * @var string The description of the composter to be shown on the composter page
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"composter"})
     */
    private $description;

    /**
     * @var string The address of the composter to be shown on the composter page
     *
     * @ORM\Column(type="text")
     * @Groups({"composter"})
     */
    private $address;

    /**
     * @var float The latitude of the composter
     *
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"composter"})
     */
    private $lat;

    /**
     * @var float The longitude of the composter
     *
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"composter"})
     */
    private $lng;

    /**
     * @var Permanence[] permancence of the composter
     *
     * @ORM\OneToMany(targetEntity="Permanence", mappedBy="composter")
     */
    private $permanences;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Commune", inversedBy="composters")
     * @Groups({"composter", "livraison"})
     */
    private $commune;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pole", inversedBy="composters")
     * @Groups({"composter"})
     */
    private $pole;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Quartier", inversedBy="composters")
     * @Groups({"composter", "livraison"})
     */
    private $quartier;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Equipement", inversedBy="composters")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"composter:admin"})
     */
    private $equipement;

    /**
     * Name of the Maitre Composter.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="mcComposters")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"composter"})
     */
    private $mc;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"composter"})
     */
    private $openingProcedures;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ApprovisionnementBroyat", inversedBy="composters")
     * @Groups({"composter"})
     */
    private $approvisionnementBroyat;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"composter"})
     */
    private $DateMiseEnRoute;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"composter"})
     */
    private $DateInauguration;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"composter"})
     */
    private $DateInstallation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\LivraisonBroyat", mappedBy="composter", orphanRemoval=true)
     * @Groups({"composter:admin"})
     */
    private $livraisonBroyats;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Suivi", mappedBy="composter", orphanRemoval=true)
     * @Groups({"composter:admin"})
     */
    private $suivis;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Reparation", mappedBy="composter")
     * @Groups({"composter:admin"})
     */
    private $reparations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Categorie", inversedBy="composteurs")
     * @Groups({"composter"})
     */
    private $categorie;

    /**
     * @ORM\Column(type="enumstatus", options={"default":"Active"})
     * @Groups({"composter"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserComposter", mappedBy="composter", orphanRemoval=true)
     */
    private $userComposters;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ComposterContact", mappedBy="composter", orphanRemoval=true)
     */
    private $composterContacts;

    /**
     * @ORM\Column(type="boolean", options={"default":1})
     * @Groups({"composter"})
     */
    private $acceptNewMembers;

    /**
     * @var MediaObject|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\MediaObject", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"composter"})
     * @ApiProperty(iri="http://schema.org/image")
     */
    private $image;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"composter"})
     */
    private $permanencesRule;

    /**
     * @ORM\Column(type="enumbroyat", options={"default":"Full"})
     * @Groups({"composter"})
     */
    private $broyatLevel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeur", inversedBy="composters")
     * @Groups({"composter:admin"})
     */
    private $financeur;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     * @Groups({"composter"})
     */
    private $serialNumber;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Contact", mappedBy="composters")
     */
    private $contacts;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"composter"})
     */
    private $publicDescription;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     * @Groups({"composter"})
     */
    private ?string $mailjetListID = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $nbFoyersPotentiels;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $nbInscrit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $nbDeposant;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $signaletiqueRond;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $signaletiquePanneau;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $hasCroc;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $hasCadenas;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $hasFourche;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $hasThermometre;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"composter:admin"})
     */
    private $hasPeson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Financeur")
     * @Groups({"composter:admin"})
     */
    private $financeurSuivi;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"composter:admin"})
     */
    private $plateNumber;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"composter"})
     */
    private $AlimentsAutorises;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"composter"})
     */
    private $AlimentsNonAutorises;

    public function __construct()
    {
        $this->permanences = new ArrayCollection();
        $this->livraisonBroyats = new ArrayCollection();
        $this->suivis = new ArrayCollection();
        $this->reparations = new ArrayCollection();
        $this->status = 'Active';
        $this->userComposters = new ArrayCollection();
        $this->composterContacts = new ArrayCollection();
        $this->acceptNewMembers = true;
        $this->broyatLevel = 'Full';
        $this->contacts = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRid(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(?float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * @return Collection|Permanence[]
     */
    public function getPermanences(): Collection
    {
        return $this->permanences;
    }

    public function addPermanence(Permanence $permanence): self
    {
        if (!$this->permanences->contains($permanence)) {
            $this->permanences[] = $permanence;
            $permanence->setComposter($this);
        }

        return $this;
    }

    public function removePermanence(Permanence $permanence): self
    {
        if ($this->permanences->contains($permanence)) {
            $this->permanences->removeElement($permanence);
            // set the owning side to null (unless already changed)
            if ($permanence->getComposter() === $this) {
                $permanence->setComposter(null);
            }
        }

        return $this;
    }

    public function getPermanencesDescription(): ?string
    {
        return $this->permanencesDescription;
    }

    public function setPermanencesDescription(?string $permanencesDescription): self
    {
        $this->permanencesDescription = $permanencesDescription;

        return $this;
    }

    public function getCommune(): ?Commune
    {
        return $this->commune;
    }

    public function setCommune(?Commune $commune): self
    {
        $this->commune = $commune;

        return $this;
    }

    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

        return $this;
    }

    public function getQuartier(): ?Quartier
    {
        return $this->quartier;
    }

    public function setQuartier(?Quartier $quartier): self
    {
        $this->quartier = $quartier;

        return $this;
    }

    public function getEquipement(): ?Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): self
    {
        $this->equipement = $equipement;

        return $this;
    }

    public function getMc(): ?User
    {
        return $this->mc;
    }

    public function setMc(?User $mc): self
    {
        $this->mc = $mc;

        return $this;
    }

    public function getOpeningProcedures(): ?string
    {
        return $this->openingProcedures;
    }

    public function setOpeningProcedures(?string $openingProcedures): self
    {
        $this->openingProcedures = $openingProcedures;

        return $this;
    }

    public function getApprovisionnementBroyat(): ?ApprovisionnementBroyat
    {
        return $this->approvisionnementBroyat;
    }

    public function setApprovisionnementBroyat(?ApprovisionnementBroyat $approvisionnementBroyat): self
    {
        $this->approvisionnementBroyat = $approvisionnementBroyat;

        return $this;
    }

    public function getDateMiseEnRoute(): ?\DateTimeInterface
    {
        return $this->DateMiseEnRoute;
    }

    public function setDateMiseEnRoute(?\DateTimeInterface $DateMiseEnRoute): self
    {
        $this->DateMiseEnRoute = $DateMiseEnRoute;

        return $this;
    }

    public function getDateInauguration(): ?\DateTimeInterface
    {
        return $this->DateInauguration;
    }

    public function setDateInauguration(?\DateTimeInterface $DateInauguration): self
    {
        $this->DateInauguration = $DateInauguration;

        return $this;
    }

    public function getDateInstallation(): ?\DateTimeInterface
    {
        return $this->DateInstallation;
    }

    public function setDateInstallation(?\DateTimeInterface $DateInstallation): self
    {
        $this->DateInstallation = $DateInstallation;

        return $this;
    }

    /**
     * @return Collection|LivraisonBroyat[]
     */
    public function getLivraisonBroyats(): Collection
    {
        return $this->livraisonBroyats;
    }

    public function addLivraisonBroyat(LivraisonBroyat $livraisonBroyat): self
    {
        if (!$this->livraisonBroyats->contains($livraisonBroyat)) {
            $this->livraisonBroyats[] = $livraisonBroyat;
            $livraisonBroyat->setComposter($this);
        }

        return $this;
    }

    public function removeLivraisonBroyat(LivraisonBroyat $livraisonBroyat): self
    {
        if ($this->livraisonBroyats->contains($livraisonBroyat)) {
            $this->livraisonBroyats->removeElement($livraisonBroyat);
            // set the owning side to null (unless already changed)
            if ($livraisonBroyat->getComposter() === $this) {
                $livraisonBroyat->setComposter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Suivi[]
     */
    public function getSuivis(): Collection
    {
        return $this->suivis;
    }

    public function addSuivi(Suivi $suivi): self
    {
        if (!$this->suivis->contains($suivi)) {
            $this->suivis[] = $suivi;
            $suivi->setComposter($this);
        }

        return $this;
    }

    public function removeSuivi(Suivi $suivi): self
    {
        if ($this->suivis->contains($suivi)) {
            $this->suivis->removeElement($suivi);
            // set the owning side to null (unless already changed)
            if ($suivi->getComposter() === $this) {
                $suivi->setComposter(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reparation[]
     */
    public function getReparations(): Collection
    {
        return $this->reparations;
    }

    public function addReparation(Reparation $reparation): self
    {
        if (!$this->reparations->contains($reparation)) {
            $this->reparations[] = $reparation;
            $reparation->setComposter($this);
        }

        return $this;
    }

    public function removeReparation(Reparation $reparation): self
    {
        if ($this->reparations->contains($reparation)) {
            $this->reparations->removeElement($reparation);
            // set the owning side to null (unless already changed)
            if ($reparation->getComposter() === $this) {
                $reparation->setComposter(null);
            }
        }

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|UserComposter[]
     */
    public function getUserComposters(): Collection
    {
        return $this->userComposters;
    }

    public function addUserComposter(UserComposter $userComposter): self
    {
        if (!$this->userComposters->contains($userComposter)) {
            $this->userComposters[] = $userComposter;
            $userComposter->setComposter($this);
        }

        return $this;
    }

    public function removeUserComposter(UserComposter $userComposter): self
    {
        if ($this->userComposters->contains($userComposter)) {
            $this->userComposters->removeElement($userComposter);
            // set the owning side to null (unless already changed)
            if ($userComposter->getComposter() === $this) {
                $userComposter->setComposter(null);
            }
        }

        return $this;
    }

    public function getFirstReferent(): ?UserComposter
    {
        $firstReferent = null;

        foreach ($this->getUserComposters() as $user) {
            if (CapabilityEnumType::REFERENT === $user->getCapability()) {
                $firstReferent = $user;
                break;
            }
        }

        return $firstReferent;
    }

    /**
     * @return Collection|ComposterContact[]
     */
    public function getComposterContacts(): Collection
    {
        return $this->composterContacts;
    }

    public function addComposterContact(ComposterContact $composterContact): self
    {
        if (!$this->composterContacts->contains($composterContact)) {
            $this->composterContacts[] = $composterContact;
            $composterContact->setComposter($this);
        }

        return $this;
    }

    public function removeComposterContact(ComposterContact $composterContact): self
    {
        if ($this->composterContacts->contains($composterContact)) {
            $this->composterContacts->removeElement($composterContact);
            // set the owning side to null (unless already changed)
            if ($composterContact->getComposter() === $this) {
                $composterContact->setComposter(null);
            }
        }

        return $this;
    }

    public function getAcceptNewMembers(): ?bool
    {
        return $this->acceptNewMembers;
    }

    public function setAcceptNewMembers(bool $acceptNewMembers): self
    {
        $this->acceptNewMembers = $acceptNewMembers;

        return $this;
    }

    public function getImage(): ?MediaObject
    {
        return $this->image;
    }

    public function setImage(?MediaObject $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getPermanencesRule(): ?string
    {
        return $this->permanencesRule;
    }

    public function setPermanencesRule(?string $permanencesRule): self
    {
        $this->permanencesRule = $permanencesRule;

        return $this;
    }

    public function getBroyatLevel()
    {
        return $this->broyatLevel;
    }

    public function setBroyatLevel($broyatLevel): self
    {
        $this->broyatLevel = $broyatLevel;

        return $this;
    }

    public function getFinanceur(): ?Financeur
    {
        return $this->financeur;
    }

    public function setFinanceur(?Financeur $financeur): self
    {
        $this->financeur = $financeur;

        return $this;
    }

    public function getSerialNumber(): ?int
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?int $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->addComposter($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
            $contact->removeComposter($this);
        }

        return $this;
    }

    public function getPublicDescription(): ?string
    {
        return $this->publicDescription;
    }

    public function setPublicDescription(?string $publicDescription): self
    {
        $this->publicDescription = $publicDescription;

        return $this;
    }

    public function getMailjetListID(): ?string
    {
        return $this->mailjetListID;
    }

    public function setMailjetListID(?string $mailjetListID): self
    {
        $this->mailjetListID = (string) $mailjetListID;

        return $this;
    }

    public function getNbFoyersPotentiels(): ?int
    {
        return $this->nbFoyersPotentiels;
    }

    public function setNbFoyersPotentiels(?int $nbFoyersPotentiels): self
    {
        $this->nbFoyersPotentiels = $nbFoyersPotentiels;

        return $this;
    }

    public function getNbInscrit(): ?int
    {
        return $this->nbInscrit;
    }

    public function setNbInscrit(?int $nbInscrit): self
    {
        $this->nbInscrit = $nbInscrit;

        return $this;
    }

    public function getNbDeposant(): ?int
    {
        return $this->nbDeposant;
    }

    public function setNbDeposant(?int $nbDeposant): self
    {
        $this->nbDeposant = $nbDeposant;

        return $this;
    }

    public function getSignaletiqueRond(): ?bool
    {
        return $this->signaletiqueRond;
    }

    public function setSignaletiqueRond(?bool $signaletiqueRond): self
    {
        $this->signaletiqueRond = $signaletiqueRond;

        return $this;
    }

    public function getSignaletiquePanneau(): ?bool
    {
        return $this->signaletiquePanneau;
    }

    public function setSignaletiquePanneau(?bool $signaletiquePanneau): self
    {
        $this->signaletiquePanneau = $signaletiquePanneau;

        return $this;
    }

    public function getHasCroc(): ?bool
    {
        return $this->hasCroc;
    }

    public function setHasCroc(?bool $croc): self
    {
        $this->hasCroc = $croc;

        return $this;
    }

    public function getHasCadenas(): ?bool
    {
        return $this->hasCadenas;
    }

    public function setHasCadenas(?bool $hasCadenas): self
    {
        $this->hasCadenas = $hasCadenas;

        return $this;
    }

    public function getHasFourche(): ?bool
    {
        return $this->hasFourche;
    }

    public function setHasFourche(?bool $hasFourche): self
    {
        $this->hasFourche = $hasFourche;

        return $this;
    }

    public function getHasThermometre(): ?bool
    {
        return $this->hasThermometre;
    }

    public function setHasThermometre(?bool $hasThermometre): self
    {
        $this->hasThermometre = $hasThermometre;

        return $this;
    }

    public function getHasPeson(): ?bool
    {
        return $this->hasPeson;
    }

    public function setHasPeson(?bool $hasPeson): self
    {
        $this->hasPeson = $hasPeson;

        return $this;
    }

    public function getFinanceurSuivi(): ?Financeur
    {
        return $this->financeurSuivi;
    }

    public function setFinanceurSuivi(?Financeur $financeurSuivi): self
    {
        $this->financeurSuivi = $financeurSuivi;

        return $this;
    }

    public function getPlateNumber(): ?string
    {
        return $this->plateNumber;
    }

    public function setPlateNumber(?string $plateNumber): self
    {
        $this->plateNumber = $plateNumber;

        return $this;
    }

    public function getAlimentsAutorises(): ?string
    {
        return $this->AlimentsAutorises;
    }

    public function setAlimentsAutorises(?string $AlimentsAutorises): self
    {
        $this->AlimentsAutorises = $AlimentsAutorises;

        return $this;
    }

    public function getAlimentsNonAutorises(): ?string
    {
        return $this->AlimentsNonAutorises;
    }

    public function setAlimentsNonAutorises(?string $AlimentsNonAutorises): self
    {
        $this->AlimentsNonAutorises = $AlimentsNonAutorises;

        return $this;
    }
}
