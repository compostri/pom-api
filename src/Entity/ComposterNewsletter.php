<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateComposterNewsletter;

/**
 * Class UserPasswordChange
 *
 * On envoie un mail a la liste mailjet du composter
 *
 * @package App\Entity
 *
 * @ApiResource(
 *     itemOperations={
 *          "get"={
 *             "controller"=NotFoundAction::class,
 *             "read"=false,
 *             "output"=false
 *          }
 *     },
 *     collectionOperations={
 *         "post"={
 *             "controller"=CreateComposterNewsletter::class,
 *              "access_control"="is_granted('ROLE_USER')"
 *          },
 *         "get"
 *     }
 * )
 */
class ComposterNewsletter
{

    /**
     * @var string id
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var Composter
     */
    private $composter;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $message;


    public function __construct()
    {
        $this->id = uniqid( 'fake-',false);
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return Composter
     */
    public function getComposter(): Composter
    {
        return $this->composter;
    }

    /**
     * @param Composter $composter
     */
    public function setComposter(Composter $composter): void
    {
        $this->composter = $composter;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }
}