<?php

// api/src/Entity/MediaObject.php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ApiResource(
 *     iri="http://schema.org/MediaObject",
 *     normalizationContext={
 *         "groups"={"media_object_read"}
 *     },
 *     collectionOperations={
 *         "post"={
 *             "access_control"="is_granted('ROLE_USER')",
 *             "validation_groups"={"Default", "media_object_create"},
 *         },
 *         "get"
 *     },
 *     itemOperations={
 *         "get",
 *         "delete"={
 *             "access_control"="is_granted('ROLE_USER')"
 *         },
 *     }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id"}, arguments={"orderParameterName"="order"})
 * @ORM\EntityListeners({"App\EventListener\MediaObjectListener"} )
 */
class MediaObject
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @ORM\Id
     * @Groups({"media_object_read", "composter"})
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var DateTime
     * @Groups({"media_object_read", "composter"})
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255 )
     * @Groups({"media_object_read", "composter"})
     */
    private $imageName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"media_object_read", "composter"})
     */
    private $imageSize;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @Groups({"media_object_read", "composter"})
     */
    private $imageDimensions = [];

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"media_object_read", "composter"})
     */
    private $imageMimeType;

    /**
     * @var string|null
     *
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"media_object_read", "composter"})
     */
    private $contentUrl;


    /**
     * @var string|null base64 image
     *
     * @Groups({"media_object_create", "composter:write"})
     */
    private $data;


    /**
     * @var File|null Fichier image
     *
     */
    private $file;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Composter", mappedBy="image")
     * @Groups({"media_object_read"})
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


    /**
     * @param File|null $file
     * @throws Exception
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;

        if ($file instanceof File) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
            $this->imageSize = $file->getSize();
            $this->imageMimeType = $file->getMimeType();
            $this->imageName = $file->getFilename();
            $this->imageDimensions = getimagesize($file->getRealPath()) ;
        }
    }

    /**
     * @param string $data
     */
    public function setData(?string $data = null): void
    {
        $this->data = $data;
    }

    public function getData(): ?string
    {
        return $this->data;
    }


    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function getImageDimensions(): ?array
    {
        return $this->imageDimensions;
    }

    public function setImageDimensions(?array $imageDimensions): self
    {
        $this->imageDimensions = $imageDimensions;

        return $this;
    }

    public function getImageMimeType(): ?string
    {
        return $this->imageMimeType;
    }

    public function setImageMimeType(?string $imageMimeType): self
    {
        $this->imageMimeType = $imageMimeType;

        return $this;
    }

    /**
     * @param string|null $contentUrl
     */
    public function setContentUrl(?string $contentUrl): void
    {
        $this->contentUrl = $contentUrl;
    }

    /**
     * @return string|null
     */
    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    /**
     * @return Collection|Composter[]
     */
    public function getComposters(): Collection
    {
        return $this->composters;
    }
}
