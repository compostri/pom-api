<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserPasswordChange;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateUserPasswordChange extends AbstractController
{
    public function __invoke(UserPasswordChange $data): UserPasswordChange
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['resetToken' => $data->getToken()]);

        if (!$user) {
            throw new BadRequestHttpException('Aucun utilisateur trouvé');
        }

        $user->setPlainPassword($data->getNewPassword());
        $user->setResetToken(null);
        $user->setEnabled(true);
        $em->persist($user);
        $em->flush();

        $data->setId($user->getId());
        $data->setToken('');
        $data->setNewPassword('');

        return $data;
    }
}
