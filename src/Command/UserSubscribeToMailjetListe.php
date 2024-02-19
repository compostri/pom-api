<?php

namespace App\Command;

use App\Entity\User;
use App\Service\Mailjet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserSubscribeToMailjetListe extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'compost:user-subscribe-mailjet-list';

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
            ->setDescription('Associe a la bonne liste chaque utilisateur qui n’a pas de mailjetId');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->em->getRepository(User::class)
            ->findUnattacheToMailJet();

        foreach ($users as $user) {
            $this->mailjet->addUser($user);
            $this->em->persist($user);
        }

        $this->em->flush();
    }
}
