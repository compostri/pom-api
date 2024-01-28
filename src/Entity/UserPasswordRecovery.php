<?php

namespace App\Entity;

use App\Controller\CreateUserPasswordRecovery;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Action\NotFoundAction;

/**
 * Class UserPasswordRecovery
 *
 * Permet de gérer le endpoint de récupération de mot de passe pour l'API
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
 *             "controller"=CreateUserPasswordRecovery::class
 *          },
 *          "get"
 *     }
 * )
 */
class UserPasswordRecovery
{
    /**
     * @var int id
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string email
     *
     */
    private $email;

    /**
     * @var string newPasswordUrl Cette url permettra de créer un liens de type {url}?token={token} qui sera envoyer par email
     *                          Cette url devra gérer la création d'un nouveau mot de passe a communiqué a l'API en même temps que le token
     *
     */
    private $newPasswordUrl;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getNewPasswordUrl(): string
    {
        return $this->newPasswordUrl;
    }

    /**
     * @param string $newPasswordUrl
     */
    public function setNewPasswordUrl(string $newPasswordUrl): void
    {
        $this->newPasswordUrl = $newPasswordUrl;
    }
}
