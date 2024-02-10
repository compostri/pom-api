<?php
/**
 * Send user notification.
 */

namespace App\Command;

use App\Entity\Permanence;
use App\Entity\User;
use App\Service\Mailjet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserNotification extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'compost:user-notification';

    private $em;

    private $mailjet;

    public function __construct(EntityManagerInterface $entityManager, Mailjet $mailjet)
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->mailjet = $mailjet;
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Send notifications to user for next permanence')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Send notifications to user for next permanence')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$entityManager = $this->getContainer()->get('doctrine')->getManager();
        $permanenceRepo = $this->em->getRepository(Permanence::class);

        $permancesToComme = $permanenceRepo->findAllToNotify();

        $messages = [];

        foreach ($permancesToComme as $perm) {
            $openers = $perm->getOpeners();
            $composter = $perm->getComposter();

            if (!$perm->getCanceled() && $composter) {
                foreach ($openers as $opener) {
                    $userComposteur = $opener->getUserCompostersFor($perm->getComposter());


                    if ($userComposteur && $userComposteur->getNotif()) {
                        $messages[] = $this->getFormatMessage($opener, $perm);
                    }
                }

                // TODO vérifier que la réponse de l'API est bien OK avant de $perm->setHasUsersBeenNotify(true)
                // Pas évident car j'ai mis la notification sur les permanence et que l'api répond par addresse mail
                $perm->setHasUsersBeenNotify(true);
                $this->em->persist($perm);
            }
        }

        if (count($messages) > 0) {
            $this->mailjet->send($messages);
        }
        $this->em->flush();
    }

    private function getFormatMessage(User $opener, Permanence $perm): array
    {
        $firstReferent = $perm->getComposter()->getFirstReferent();

        $formattedMessage = [
            'to' => [
                [
                    'email' => $opener->getEmail(), // $mail
                    'name' => $opener->getUsername(),
                ],
            ],
            'templateId' => (int) getenv('MJ_NOTIFICATION_TEMPLATE_ID'),
            'subject' => "[{$perm->getComposter()->getName()}] C'est bientôt à vous d'ouvrir",
            'params' => [
                'prenom' => $opener->getFirstname(),
                'date' => $perm->getDate()->format('d/m/Y'),
                'openingProcedure' => $perm->getComposter()->getOpeningProcedures(),
            ],
        ];

        if ($firstReferent) {
            $formattedMessage['replyTo'] = [
                'email' => $firstReferent->getUser()->getEmail(),
                'name' => $firstReferent->getUser()->getUsername(),
            ];
        }

        return $formattedMessage;
    }
}
