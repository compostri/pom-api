<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"},message="Cette email est déjà attribué à un utilisateur existant")
 * @ORM\EntityListeners({"App\EventListener\UserListener"})
 *
 * @ApiResource(
 *     attributes={"security"="is_granted('ROLE_USER')"},
 *     normalizationContext={"groups"={"user", "user:read"}},
 *     denormalizationContext={"groups"={"user", "user:write"}}
 *
 * )
 * @ApiFilter(SearchFilter::class, properties={
 *   "username"     : "partial",
 *   "email"        : "partial",
 *   "firstname"    : "partial",
 *   "lastname"     : "partial",
 *   "roles"        : "partial",
 *   "phone"        : "partial",
 *   "userComposters.composter.name" : "partial"
 * })
 * @ApiFilter(BooleanFilter::class, properties={"enabled"})
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"permanence", "composter", "userComposter"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180)
     * @Groups({"user","permanence", "composter", "userComposter"})
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user","permanence", "composter", "userComposter"})
     * @Assert\Email
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     * @Groups({"user"})
     */
    private $roles;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Groups({"user:write", "userComposter:write"})
     */
    private $plainPassword;


    /**
     * @Groups({"user:write"})
     */
    private $oldPassword;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Permanence", inversedBy="users")
     */
    private $permanences;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Composter", mappedBy="mc")
     */
    private $mcComposters;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserComposter", mappedBy="user", orphanRemoval=true)
     * @Groups({"user:read"})
     */
    private $userComposters;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     * @Groups({"user", "userComposter"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     * @Groups({"user", "userComposter"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetToken;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     * @Groups({"user", "userComposter"})
     */
    private $enabled;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string userConfirmedAccountURL Url pour gérer la confirmation du compte.
     *      Un lien sera créé et envoyé à cette URL de type {userConfirmedAccountURL}?token=token
     *      Il faudra utiliser le endpoint `user_password_changes` et renvoyer un mot de passe et le token
     *      Cela aura pour effet de vérifier le compte ( passer enabled a true )
     * @Groups({"user:write", "userComposter:write"})
     */
    private $userConfirmedAccountURL;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user", "userComposter"})
     */
    private $phone;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $mailjetId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user", "userComposter"})
     */
    private $role;

    /**
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $lastUpdateDate;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":"0"})
     * @Groups({"user"})
     */
    private $hasFormationReferentSite;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default":"0"})
     * @Groups({"user"})
     */
    private $hasFormationGuideComposteur;

    /**
     * @ORM\Column(type="boolean", options={"default":"0"})
     * @Groups({"user","userComposter:write"})
     */
    private $isSubscribeToCompostriNewsletter;


    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->permanences = new ArrayCollection();
        $this->mcComposters = new ArrayCollection();
        $this->userComposters = new ArrayCollection();
        $this->enabled = false;
        $this->lastUpdateDate = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword(string $oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        // forces the object to look "dirty" to Doctrine. Avoids
        // Doctrine *not* saving this entity, if only plainPassword changes
        $this->setLastUpdateDate(new \DateTime());
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
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
        }

        return $this;
    }

    public function removePermanence(Permanence $permanence): self
    {
        if ($this->permanences->contains($permanence)) {
            $this->permanences->removeElement($permanence);
        }

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|Composter[]
     */
    public function getMcComposters(): Collection
    {
        return $this->mcComposters;
    }

    public function addMcComposter(Composter $mcComposter): self
    {
        if (!$this->mcComposters->contains($mcComposter)) {
            $this->mcComposters[] = $mcComposter;
            $mcComposter->setMc($this);
        }

        return $this;
    }

    public function removeMcComposter(Composter $mcComposter): self
    {
        if ($this->mcComposters->contains($mcComposter)) {
            $this->mcComposters->removeElement($mcComposter);
            // set the owning side to null (unless already changed)
            if ($mcComposter->getMc() === $this) {
                $mcComposter->setMc(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserComposter[]
     */
    public function getUserComposters(): Collection
    {
        return $this->userComposters;
    }

    /**
     * @param Composter $composter
     * @return null|UserComposter
     */
    public function getUserCompostersFor(Composter $composter): ?UserComposter
    {
        $userComposter = null;
        foreach ($this->getUserComposters() as $uc) {
            if ($uc->getComposter()->getId() === $composter->getId()) {
                $userComposter = $uc;
                break;
            }
        }

        return $userComposter;
    }

    public function addUserComposter(UserComposter $userComposter): self
    {
        if (!$this->userComposters->contains($userComposter)) {
            $this->userComposters[] = $userComposter;
            $userComposter->setUser($this);
        }

        return $this;
    }

    public function removeUserComposter(UserComposter $userComposter): self
    {
        if ($this->userComposters->contains($userComposter)) {
            $this->userComposters->removeElement($userComposter);
            // set the owning side to null (unless already changed)
            if ($userComposter->getUser() === $this) {
                $userComposter->setUser(null);
            }
        }

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getUserConfirmedAccountURL(): ?string
    {
        return $this->userConfirmedAccountURL;
    }

    /**
     * @param string $userConfirmedAccountURL
     */
    public function setUserConfirmedAccountURL(string $userConfirmedAccountURL): self
    {
        $this->userConfirmedAccountURL = $userConfirmedAccountURL;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getLastUpdateDate(): ?\DateTimeInterface
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateDate(\DateTimeInterface $lastUpdateDate): self
    {
        $this->lastUpdateDate = $lastUpdateDate;

        return $this;
    }

    public function getMailjetId(): ?int
    {
        return $this->mailjetId;
    }

    public function setMailjetId(?int $mailjetId): self
    {
        $this->mailjetId = $mailjetId;

        return $this;
    }

    public function getHasFormationReferentSite(): ?bool
    {
        return $this->hasFormationReferentSite;
    }

    public function setHasFormationReferentSite(?bool $hasFormationReferentSite): self
    {
        $this->hasFormationReferentSite = $hasFormationReferentSite;

        return $this;
    }

    public function getHasFormationGuideComposteur(): ?bool
    {
        return $this->hasFormationGuideComposteur;
    }

    public function setHasFormationGuideComposteur(?bool $hasFormationGuideComposteur): self
    {
        $this->hasFormationGuideComposteur = $hasFormationGuideComposteur;

        return $this;
    }

    public function getIsSubscribeToCompostriNewsletter(): ?bool
    {
        return $this->isSubscribeToCompostriNewsletter;
    }

    public function setIsSubscribeToCompostriNewsletter(bool $isSubscribeToCompostriNewsletter): self
    {
        $this->isSubscribeToCompostriNewsletter = $isSubscribeToCompostriNewsletter;

        return $this;
    }
}
