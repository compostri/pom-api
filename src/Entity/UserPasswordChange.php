<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\CreateUserPasswordChange;
use ApiPlatform\Core\Action\NotFoundAction;

/**
 * Class UserPasswordChange
 *
 * Change le mot de passe pour un token (lié a un User ) demander
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
 *             "controller"=CreateUserPasswordChange::class
 *          },
 *         "get"
 *     }
 * )
 */
class UserPasswordChange
{
    /**
     * @var int id
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string token Envoyé par mail avec le endpoint `/user_password_recoveries`
     *
     */
    private $token;

    /**
     * @var string newPassword Nouveau mot de passe pour le User lié aux token
     *
     */
    private $newPassword;

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
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    /**
     * @param string $newPassword
     */
    public function setNewPassword(string $newPassword): void
    {
        $this->newPassword = $newPassword;
    }
}
