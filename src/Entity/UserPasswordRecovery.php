<?php

namespace App\Entity;

use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateUserPasswordRecovery;

/**
 * Class UserPasswordRecovery.
 *
 * Permet de gérer le endpoint de récupération de mot de passe pour l'API
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
     */
    private $email;

    /**
     * @var string newPasswordUrl Cette url permettra de créer un liens de type {url}?token={token} qui sera envoyer par email
     *             Cette url devra gérer la création d'un nouveau mot de passe a communiqué a l'API en même temps que le token
     */
    private $newPasswordUrl;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getNewPasswordUrl(): string
    {
        return $this->newPasswordUrl;
    }

    public function setNewPasswordUrl(string $newPasswordUrl): void
    {
        $this->newPasswordUrl = $newPasswordUrl;
    }
}
