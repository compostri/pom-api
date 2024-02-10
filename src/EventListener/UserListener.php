<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\Mailjet;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserListener
{
    protected $encoder;
    protected $em;
    protected $tokenGenerator;
    protected $email;

    /**
     * UserListener constructor.
     */
    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager, TokenGeneratorInterface $tokenGenerator, Mailjet $email)
    {
        $this->encoder = $encoder;
        $this->em = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
        $this->email = $email;
    }

    public function prePersist(User $user): void
    {
        $this->email->addUser($user);

        /*
         * Pour les utilisateur nouvellement créer qui sont en enabled = false :
         *  1. On crée un token
         */
        if (!$user->getEnabled() && $user->getUserConfirmedAccountURL()) {
            $resetToken = $this->tokenGenerator->generateToken();
            $user->setResetToken($resetToken);
        }

        $this->encodePassword($user);
    }

    /**
     * @throws \Exception
     */
    public function preUpdate(User $user): void
    {
        if ($user->getOldPassword() && !$this->encoder->isPasswordValid($user, $user->getOldPassword())) {
            throw new BadRequestHttpException('L’ancien mot de passe n’est pas le bon');
        }

        $user->setLastUpdateDate(new \DateTime());
        $this->encodePassword($user);

        // necessary to force the update to see the change
        $meta = $this->em->getClassMetadata(get_class($user));
        $this->em->getUnitOfWork()->recomputeSingleEntityChangeSet($meta, $user);
    }

    public function postUpdate(User $user, LifecycleEventArgs $eventArgs)
    {
        // Si on change l'abonnement a la newsletter on envoie l'information a MailJet
        $changeSet = $eventArgs->getEntityManager()->getUnitOfWork()->getEntityChangeSet($user);
        if (isset($changeSet['isSubscribeToCompostriNewsletter'])) {
            if ($user->getIsSubscribeToCompostriNewsletter()) {
                $this->email->addUser($user);

                // Sauvegarder l'id
                $this->em->persist($user);
                $this->em->flush();
            } else {
                $this->email->removeFromList($user->getMailjetId(), [getenv('MJ_COMPOSTRI_NEWSLETTER_CONTACT_LIST_ID')]);
            }
        }
    }

    private function encodePassword(User $user): void
    {
        if (!$user->getPlainPassword()) {
            return;
        }

        $encoded = $this->encoder->encodePassword(
            $user,
            $user->getPlainPassword()
        );

        $user->setPassword($encoded);
    }
}
