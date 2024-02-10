<?php

namespace App\Command;

use App\DBAL\Types\CapabilityEnumType;
use App\DBAL\Types\ContactEnumType;
use App\DBAL\Types\StatusEnumType;
use App\Entity\Commune;
use App\Entity\Composter;
use App\Entity\Contact;
use App\Entity\Equipement;
use App\Entity\User;
use App\Entity\UserComposter;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompostImportComposteurLabelverteCommand extends Command
{
    protected static $defaultName = 'compost:import-composteur-labelverte';

    private OutputInterface $output;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Import all composter from ods file')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Import all composter from ods file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'path du fichier ODS a importer');
    }

    /**
     * @throws ReaderNotOpenedException
     * @throws IOException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $filePath = $input->getArgument('filePath');

        $reader = ReaderEntityFactory::createODSReader();

        $reader->open($filePath);
        $composterCount = 0;

        $rowToImportBySheet = [
            1 => [6, 29], // 2009
            2 => [6, 30], // 2010
            3 => [6, 21], // 2011
            4 => [6, 16], // 2012
            5 => [6, 22], // 2013
            6 => [6, 23], // 2014
            7 => [6, 21], // 2015
            8 => [6, 22], // 2016
            9 => [6, 24], // 2017
            10 => [6, 24], // 2018
            11 => [6, 52], // 2019
            12 => [6, 37], // 2020
            13 => [6, 41], // 2021
            14 => [6, 10], // 2022
        ];
        foreach ($reader->getSheetIterator() as $key => $sheet) {
            if (in_array($key, array_keys($rowToImportBySheet))) {
                /**
                 * @var Row $row
                 */
                foreach ($sheet->getRowIterator() as $rkey => $row) {
                    $cells = $row->getCells();

                    // Les trois premières lignes du doc sont des entête
                    if ($rkey > ($rowToImportBySheet[$key][0] - 1) && $rkey <= $rowToImportBySheet[$key][1]) {
                        $composteur = $this->getComposteurs($cells, $key + 2008);
                        if ($composteur) {
                            $this->em->persist($composteur);

                            ++$composterCount;
                        }
                    }
//                    else {
//                        foreach ( $cells as $index => $cell){
//                            if( $cell->getValue() === 'Modèle'){
//                                $output->writeln("c la colone {$index}");
//                            }
//                            if( $cell->getValue() === 'Contenance'){
//                                $output->writeln("c la colone {$index}");
//                                die;
//                            }
//                        }
//                    }
                }
            }
        }

        $this->em->flush();

        $output->writeln("Import de {$composterCount} composteurs");

        $reader->close();
    }

    /**
     * @param Cell[] $cells
     *
     * @return Composter
     */
    private function getComposteurs($cells, int $installationYear): ?Composter
    {
        $plateNumber = (string) $cells[0];
        $composteurRepo = $this->em->getRepository(Composter::class);
        $composteur = $composteurRepo->findOneBy(['plateNumber' => $plateNumber]);

        if (!$composteur) {
            $composteur = new Composter();
        }

        $dateIgnoguration = $cells[70]->isDate() ? $cells[70]->getValue() : null;

        $composteur
            ->setPlateNumber($plateNumber)
            ->setMc($this->getUser((string) $cells[1]))
            ->setCommune($this->getCommune((string) $cells[6]))
            ->setName((string) $cells[3])
            ->setAddress((string) $cells[4])
            ->setDateInstallation(new \DateTime("01-01-{$installationYear}"))
            ->setDateInauguration($dateIgnoguration)
            ->setEquipement(!$cells[76]->isDate() && !$cells[77]->isDate() ? $this->getEquipement((string) $cells[76], (string) $cells[77]) : null)
            ->setMailjetListID(10)
            ->setStatus(StatusEnumType::ACTIVE)
        ;

        $referent = $this->getReferent((string) $cells[12], (string) $cells[13], $composteur);
        if ($referent) {
            $composteur->addUserComposter($referent);
        }

        $contact1 = $this->getContact((string) $cells[15], (string) $cells[16], 'Gestionnaire de copropriété / représentant du bailleur');
        if ($contact1) {
            $composteur->addContact($contact1);
        }
        $contact2 = $this->getContact((string) $cells[17], (string) $cells[18], 'Président(e) du c. s. de copropriété');
        if ($contact2) {
            $composteur->addContact($contact2);
        }
        $contact3 = $this->getContact((string) $cells[22], (string) $cells[23], 'Gardien d’immeuble');
        if ($contact3) {
            $composteur->addContact($contact3);
        }

        return $composteur;
    }

    private function getUser(string $fullName, array $role = ['ROLE_ADMIN'], string $phone = null): ?User
    {
        if (empty($fullName)) {
            return null;
        }

        $fullNameArray = explode(' ', $fullName);

        if (count($fullNameArray) < 2) {
            $this->output->writeln($fullName);

            return null;
        }
        $firstName = $fullNameArray[0];
        $LastName = $fullNameArray[1];

        $userRepo = $this->em->getRepository(User::class);
        $mc = $userRepo->findOneBy(['firstname' => $firstName, 'lastname' => $LastName]);

        if (!$mc) {
            $mc = new User();
            $mc
                ->setFirstname($firstName)
                ->setLastname($LastName)
                ->setUsername($firstName)
                ->setemail("{$firstName}.{$LastName}@labelverte.fr")
                ->setRoles($role)
                ->setPhone($phone)
                ->setPlainPassword('tobechanged')
                ->setUserConfirmedAccountURL(getenv('FRONT_DOMAIN').'/confirmation')
                ->setIsSubscribeToCompostriNewsletter(false)
                ->setEnabled(true)
                ->setMailjetId(10)
            ;

            $this->em->persist($mc);
            $this->em->flush();
        }

        return $mc;
    }

    public function getCommune(string $communeName): ?Commune
    {
        $communeName = trim($communeName);

        if (empty($communeName)) {
            return null;
        }

        // Uniformisation des noms de commune
        switch ($communeName) {
            case 'Saint Barthélémy d’Anjou':
            case 'SAINT BARTHELEMY D\'ANJOU':
                $communeName = 'Saint-Barthélemy-d\'Anjou';
                break;
            case 'SAINT SYLVAIN D\'ANJOU':
                $communeName = 'Saint-Sylvain d\'Anjou';
                break;
            case 'LES PONTS DE CE':
                $communeName = 'Les Ponts-de-Cé';
                break;
            case 'LE PLESSIS-GRAMMOIRE':
                $communeName = 'Le Pléssis-grammoire';
                break;
            case 'SAINTE GEMMES SUR LOIRE':
                $communeName = 'Sainte-Gemmes-sur-Loire';
                break;
            case 'SAVENNIERES':
                $communeName = 'Savennières';
                break;
            case 'MURS ERIGNE':
                $communeName = 'Mûrs-Érigné';
                break;
        }

        $userRepo = $this->em->getRepository(Commune::class);
        $commune = $userRepo->findOneBy(['name' => $communeName]);

        if (!$commune) {
            $commune = new Commune();

            $commune->setName($communeName);
            $this->em->persist($commune);
            $this->em->flush();
        }

        return $commune;
    }

    private function getReferent(string $referentName, string $referentPhone, Composter $composter): ?UserComposter
    {
        $user = $this->getUser(
            $referentName,
            ['ROLE_USER'],
            $this->cleanPhoneNumber($referentPhone)
        );

        if (!$user) {
            return null;
        }

        $ucr = $this->em->getRepository(UserComposter::class);
        $userComposter = $ucr->findOneBy(['user' => $user, 'composter' => $composter]);

        if (!$userComposter) {
            $userComposter = new UserComposter();
            $userComposter
                ->setUser($user)
                ->setCapability(CapabilityEnumType::REFERENT)
                ->setComposterContactReceiver(true)
                ->setNewsletter(false)
                ->setNotif(true)
            ;

            $this->em->persist($userComposter);
        }

        return $userComposter;
    }

    private function cleanPhoneNumber(string $phoneNumber): ?string
    {
        return 'X' !== $phoneNumber ? str_replace([' ', '-'], '', $phoneNumber) : null;
    }

    private function getContact(string $fullName, string $phone, string $role): ?Contact
    {
        if (empty($fullName) || in_array($fullName, ['x', 'X', '/', 'oui', 'Non'])) {
            return null;
        }

        $fullNameArray = explode(' ', $fullName);

        if (count($fullNameArray) < 2) {
            $this->output->writeln($fullName);

            return null;
        }
        $firstName = $fullNameArray[0];
        $LastName = $fullNameArray[1];

        $contactRepo = $this->em->getRepository(Contact::class);
        $contact = $contactRepo->findOneBy(['firstName' => $firstName, 'lastName' => $LastName]);

        if (!$contact) {
            $contact = new Contact();
            $contact
                ->setFirstname($firstName)
                ->setLastname($LastName)
                ->setPhone($this->cleanPhoneNumber($phone))
                ->setRole($role)
                ->setEmail("{$firstName}.{$LastName}@labelverte.fr")
                ->setContactType(ContactEnumType::SYNDIC)
            ;

            $this->em->persist($contact);
            $this->em->flush();
        }

        return $contact;
    }

    private function getEquipement(string $type, string $capacity): ?Equipement
    {
        if (empty($type)) {
            return null;
        }

        $er = $this->em->getRepository(Equipement::class);
        $equipement = $er->findOneBy(['type' => $type, 'capacite' => $capacity]);

        if (!$equipement) {
            $equipement = new Equipement();
            $equipement
                ->setCapacite($capacity)
                ->setType($type)
            ;

            $this->em->persist($equipement);
            $this->em->flush();
        }

        return $equipement;
    }
}
