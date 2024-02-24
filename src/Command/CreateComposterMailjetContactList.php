<?php

namespace App\Command;

use App\Entity\Composter;
use App\Service\Mailjet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateComposterMailjetContactList extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'compost:create-mailjet-contact-list';

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
            ->setDescription('Create a Mailjet contact list foreach composter')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Create a Mailjet contact list foreach composter');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composters = $this->em->getRepository(Composter::class)
            ->findAllWithUsers();

        foreach ($composters as $composter) {
            if ($composter instanceof Composter) {
                $composter = $this->mailjet->createComposterContactList($composter);
                $this->em->persist($composter);
                $output->writeln('Nouvelle liste crée pour le composter ['.$composter->getName().'] : '.$composter->getMailjetListID());
            }
        }
        $this->em->flush();
    }
}
