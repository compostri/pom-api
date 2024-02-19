<?php

namespace App\Command;

use App\DBAL\Types\CapabilityEnumType;
use App\DBAL\Types\ContactEnumType;
use App\DBAL\Types\StatusEnumType;
use App\Entity\ApprovisionnementBroyat;
use App\Entity\Categorie;
use App\Entity\Commune;
use App\Entity\Composter;
use App\Entity\Contact;
use App\Entity\Equipement;
use App\Entity\Financeur;
use App\Entity\Pole;
use App\Entity\Quartier;
use App\Entity\Reparation;
use App\Entity\Suivi;
use App\Entity\User;
use App\Entity\UserComposter;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportComposter extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'compost:import-composteur';

    private $em;
    private $output;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $filePath = $input->getArgument('filePath');

        $reader = ReaderEntityFactory::createODSReader();

        $reader->open($filePath);
        $composterCount = 0;
        foreach ($reader->getSheetIterator() as $key => $sheet) {
            if (1 === $key) {
                foreach ($sheet->getRowIterator() as $rkey => $row) {
                    // Les trois premières lignes du doc sont des entête
//                    if ($rkey > 3) {
//
//                        $cells = $row->getCells();
//                        $this->importOnglet1( $cells );
//                        $this->em->flush();
//
//
//                        $composterCount++;
//                    }
                }
            } elseif (2 === $key) {
                foreach ($sheet->getRowIterator() as $rkey => $row) {
                    // Les deux premières lignes du doc sont des entête
                    if ($rkey > 3) {
                        $cells = $row->getCells();
                        $this->importOnglet2($cells);
                        $this->em->flush();
                        ++$composterCount;
                    }
                }
            } elseif (3 === $key) {
//                foreach ($sheet->getRowIterator() as $rkey => $row) {
//
//                    // Les deux premières lignes du doc sont des entête
//                    if ($rkey > 2) {
//
//                        $cells = $row->getCells();
//                        $this->importOnglet3( $cells );
//                        $this->em->flush();
//                    }
//                }
            }
        }
        $output->writeln("Import de {$composterCount} composteurs");

        $reader->close();
    }

    /**
     * @param $cells
     *
     * @throws Exception
     */
    private function importOnglet5($cells): void
    {
        $composterRepository = $this->em->getRepository(Composter::class);
        $pavillonsRepository = $this->em->getRepository(Equipement::class);
        $communeRepository = $this->em->getRepository(Commune::class);
        $catRepository = $this->em->getRepository(Categorie::class);

        $name = $cells[1]->getValue();
        if (empty($name) || 'NOM DU SITE' === $name) {
            return;
        }

        $composter = $composterRepository->findOneBy(['name' => $name]);
        if (!$composter) {
            $this->output->writeln("Pas trouvé le composter '{$name}'");
            $composter = new Composter();
            $composter->setName($name);
            $composter->setAddress('');
        }

        // Status
        $status = $cells[7]->getValue();
        $cat = $catRepository->find(1);
        switch ($status) {
            case 'déplacé':
                $status = StatusEnumType::MOVED;
                break;
            case 'supprimé':
            case 'existant réaffecté':
                $status = StatusEnumType::DELETE;
                break;
            case 'à déplacer':
            case 'à déplacer ?':
                $status = StatusEnumType::TO_BE_MOVED;
                break;
            case 'en dormance':
                $status = StatusEnumType::DORMANT;
                break;
            case 'composteur école et quartier en dormance':
            case 'composteur école en dormance':
                $status = StatusEnumType::DORMANT;
                $cat = $catRepository->find(3);
                break;
        }

        $composter->setCategorie($cat);
        $composter->setStatus($status);

        // Equipement
        $volumeName = $cells[2]->getValue();
        $pavillonsVolume = $pavillonsRepository->findOneBy(['volume' => $volumeName]);
        if (!$pavillonsVolume) {
            $pavillonsVolume = new Equipement();
            $pavillonsVolume->setVolume($volumeName);
            $this->em->persist($pavillonsVolume);
            $this->em->flush();
            $this->output->writeln("Equipement créée : '{$volumeName}'");
        }
        $composter->setPavilionsVolume($pavillonsVolume);

        // Commune
        $communeName = $cells[3]->getValue();

        $commune = $communeRepository->findOneBy(['name' => $communeName]);
        if (!$commune) {
            $commune = new Commune();
            $commune->setName($communeName);
            $this->em->persist($commune);
            $this->em->flush();
        }
        $composter->setCommune($commune);

        // Description
        $oldDescription = $composter->getDescription();
        $newDescription = $cells[4]->getValue();
        if ($newDescription instanceof DateTime) {
            $newDescription = $newDescription->format('d/m/Y');
        }
        $newDescription .= "\n{$cells[5]->getValue()}";
        $newDescription .= "\n{$cells[6]->getValue()}";

        if ($oldDescription) {
            $newDescription = "{$oldDescription}\n{$newDescription}";
        }

        $composter->setDescription($newDescription);

        // Persist
        $this->em->persist($composter);
    }

    /**
     * @param $cells
     *
     * @throws Exception
     */
    private function importOnglet4($cells): void
    {
        $catRepository = $this->em->getRepository(Categorie::class);

        $name = $cells[1]->getValue();
        if (empty($name)) {
            return;
        }

        $composter = $this->getComposterByName($name);

        // Status
        $status = 'En dormance' === $cells[0]->getValue() ? StatusEnumType::DORMANT : StatusEnumType::IN_PROJECT;
        $composter->setStatus($status);

        // Addresse
        $composter->setAddress($cells[2]->getValue());

        // Commune
        $commune = $this->getCommuneByName($cells[3]->getValue());
        $composter->setCommune($commune);

        // Quartier
        $quartierName = $cells[4]->getValue();
        if (!empty($quartierName)) {
            $quartier = $this->getQuartierByName($quartierName);
            $composter->setQuartier($quartier);
        }

        // TODO Import finnanceur

        // Catégorie
        $composter->setCategorie($catRepository->find(1));

        // Equipement
        $pavillons = $this->getPavillonsByVolume($cells[13]->getValue());
        if ($pavillons) {
            $composter->setPavilionsVolume($pavillons);
        }

        // Description
        $composter->setDescription($cells[31]->getValue());

        // Persist
        $this->em->persist($composter);
    }

    /**
     * @param $cells
     *
     * @throws Exception
     */
    private function importOnglet3($cells): void
    {
        $composterRepository = $this->em->getRepository(Composter::class);

        $name = $cells[1]->getValue();
        // Les noms modifier
        if ('Barbonnerie' === $name) {
            $name = 'Barbonnerie 2';
        } elseif ('Place Emile Fritsch') {
            $name = 'Saint Pasquier';
        } elseif ('La Renaudière') {
            $name = 'Renaudière';
        } elseif ('Guinaudeau') {
            $name = 'Guinaudeau > 15 Lieux';
        } elseif ('Waldeck Rousseau') {
            $name = 'Waldeck';
        } elseif ('Lamour les forges') {
            $name = 'Venelles Lamour-Les Forges';
        } elseif ('Crapaudine') {
            $name = 'Jardin de la Crapaudine';
        } elseif ('St Pasquier – Émile Fritsh') {
            $name = 'Saint Pasquier';
        }
        $composter = $composterRepository->findOneBy(['name' => $name]);
        if (!$composter) {
            $this->output->writeln("Pas trouvé le composter '{$name}'");
        } else {
            $reparation = new Reparation();

            $date = $this->getDateStringFromFile($cells[0]);
            if (!$date) {
                $date = $cells[8]->isEmpty() ? new DateTime('2019-06-26') : new DateTime('2018/06/26');
            }
            $reparationDescription = !$cells[8]->isEmpty() ? $cells[8]->getValue() : $cells[11]->getValue();
            $refFacture = !$cells[9]->isEmpty() ? $cells[9]->getValue() : $cells[12]->getValue();
            $montant = !$cells[10]->isEmpty() ? $cells[10]->getValue() : $cells[13]->getValue();
            $montant = (float) str_replace(',', '.', $montant);

            $nature = null;
            if (!$cells[5]->isEmpty()) {
                $nature = 'Dégradation usuelle';
            } elseif (!$cells[6]->isEmpty()) {
                $nature = 'Dégradation vandalisme';
            } elseif (!$cells[7]->isEmpty()) {
                $nature = 'Aménagement';
            }

            $reparation->setDate($date);
            $reparation->setDescription($reparationDescription);
            $reparation->setRefFacture($refFacture);
            $reparation->setMontant(0 !== $montant ? $montant : null);
            $reparation->setDone(true);
            $reparation->setComposter($composter);
            $reparation->setNature($nature);

            $this->em->persist($reparation);
        }
    }

    /**
     * @param Cell[] $cells
     *
     * @throws Exception
     */
    private function importOnglet2(array $cells): void
    {
        $composterRepository = $this->em->getRepository(Composter::class);
        $approvisionnementBroyatRepository = $this->em->getRepository(ApprovisionnementBroyat::class);

        $serialNumber = $cells[0]->getValue();
        $name = $cells[1]->getValue();
        $name = str_replace('’', '\'', $name);
        $composter = $composterRepository->findOneBy(['serialNumber' => $serialNumber]);

        if (!$composter) {
            // Au cas ou on essaie de retrouver le composteur par son nom
            $composter = $this->getComposterByName($name);
            $composter->setSerialNumber($serialNumber);
        }

        // Procédure d'ouverture
//        $apport     = $cells[12]->getValue();
//        $maturation = $cells[13]->getValue();
//        $broyat     = $cells[14]->getValue();
//        $openingProcedures = "Bac d’apport : {$apport}, bac de maturation : $maturation, bac de broyat : {$broyat}";
//        $composter->setOpeningProcedures( $openingProcedures );

        // Fréquentation / Capacité
        $nbFoyersPotentiels = $cells['26']->getValue();
        $nbInscrit = $cells['27']->getValue();
        $nbDeposant = $cells['28']->getValue();
        if (is_numeric($nbFoyersPotentiels)) {
            $composter->setNbFoyersPotentiels((int) $nbFoyersPotentiels);
        }
        if (is_numeric($nbInscrit)) {
            $composter->setNbInscrit((int) $nbInscrit);
        }
        if (is_numeric($nbDeposant)) {
            $composter->setNbDeposant((int) $nbDeposant);
        }

        // Suivi
//        $suiviDescription = $cells[33]->getValue();
//        if( $suiviDescription ){
//
//            $suiviDate = $cells[21]->getValue();
//            if( ! $suiviDate instanceof DateTime ){
//                $suiviDate = new DateTime();
//            }
//
//            // Dynamisme
//            $animation      = $cells[29]->getValue();
//            $environnement  = $cells[30]->getValue();
//            $technique	    = $cells[31]->getValue();
//            $autonomie      = $cells[32]->getValue();
//
//            $suivi = new Suivi();
//            $suivi->setDescription( $suiviDescription );
//            $suivi->setComposter( $composter );
//            $suivi->setDate( $suiviDate );
//            if( is_numeric( $animation ) ) { $suivi->setAnimation( (int) $animation ); }
//            if( is_numeric( $environnement ) ) { $suivi->setEnvironnement( (int) $environnement ); }
//            if( is_numeric( $technique ) ) { $suivi->setTechnique( (int) $technique ); }
//            if( is_numeric( $autonomie ) ) { $suivi->setAutonomie( (int) $autonomie ); }
//
//            $this->em->persist( $suivi );
//        }

//        // approvisionnement Broyat
//        $appBroyatName = false;
//        if( $cells[43]->getValue() === 'X' ){
//            $appBroyatName = 'Autonomie';
//        } else if( $cells[44]->getValue() === 'X' ){
//            $appBroyatName = 'Compostri';
//        } else if( $cells[45]->getValue() === 'X' ){
//            $appBroyatName = 'Alisé';
//        } else if( $cells[46]->getValue() === 'X' ){
//            $appBroyatName = 'Ville';
//        } else if( $cells[47]->getValue() === 'X' ){
//            $appBroyatName = 'Séquoïa';
//        }
//
//        if( $appBroyatName ){
//
//
//            $approvisionnementBroyat = $approvisionnementBroyatRepository->findOneBy( [ 'name' => $appBroyatName ] );
//
//            if( ! $approvisionnementBroyat ){
//                $approvisionnementBroyat = new ApprovisionnementBroyat();
//                $approvisionnementBroyat->setName( $appBroyatName );
//                $this->em->persist( $approvisionnementBroyat );
//                $this->em->flush();
//            }
//
//            $composter->setApprovisionnementBroyat( $approvisionnementBroyat );
//        }
//
//

//        // Reparation
//        $reparationDescription = $cells[59]->getValue();
//        if( $reparationDescription && ! empty( $reparationDescription )){
//
//            $reparation = new Reparation();
//            $reparation->setDescription( $reparationDescription );
//            $reparation->setComposter( $composter );
//            $reparation->setDone( false );
//
//            $this->em->persist( $reparation );
//        }
    }

    /**
     * Import des composteurs.
     *
     * @param $cells
     *
     * @throws Exception
     */
    private function importOnglet1($cells): void
    {
        $composterRepository = $this->em->getRepository(Composter::class);
        $categorieRepository = $this->em->getRepository(Categorie::class);
        $poleRepository = $this->em->getRepository(Pole::class);
        $userRepository = $this->em->getRepository(User::class);
        $financeurRepository = $this->em->getRepository(Financeur::class);

        // On crée ou récupére un composter par serialNumber ou name
        $serialNumber = $cells[2]->getValue();
        $name = $cells[3]->getValue();
        $name = str_replace('’', '\'', $name);
        $composter = $composterRepository->findOneBy(['serialNumber' => $serialNumber]);

        if (!$composter) {
            // Au cas ou on essaie de retrouver le composteur par son nom
            $composter = $this->getComposterByName($name);
            $composter->setSerialNumber($serialNumber);
        }

        // Financeur
        $financeurInitiales = $cells[0]->getValue();

        if (empty($financeurInitiales) || strlen($financeurInitiales) > 5) {
            if ('Supprimé' === $financeurInitiales) {
                $composter->setStatus(StatusEnumType::DELETE);
            } elseif ('Remplacé' === $financeurInitiales) {
                $composter->setStatus(StatusEnumType::MOVED);
            } elseif (0 === strpos($financeurInitiales, 'Supprimé/Déplacé')) {
                $composter->setStatus(StatusEnumType::MOVED);
            } elseif ('Déplacé à Babhoneur' === $financeurInitiales) {
                $composter->setStatus(StatusEnumType::MOVED);
            } else {
                $this->output->writeln("Financeur pas compatible : {$financeurInitiales}");
            }
        } else {
            $composter->setStatus(StatusEnumType::ACTIVE);
            $financeur = $financeurRepository->findOneBy(['initials' => $financeurInitiales]);

            if (!$financeur) {
                switch ($financeurInitiales) {
                    case 'NM':
                        $financeurName = 'Nantes Métropole';
                        break;
                    case 'VDSH':
                        $financeurName = 'Ville de Saint Herblain';
                        break;
                    case 'VDN':
                        $financeurName = 'Ville de Nantes';
                        break;
                    case 'NMH':
                        $financeurName = 'Nantes Métropole Habitat';
                        break;
                    case 'VDSS':
                        $financeurName = 'Ville de Saint Sébastien';
                        break;
                    default:
                        $financeurName = $financeurInitiales;
                }

                $financeur = new Financeur();
                $financeur->setInitials($financeurInitiales);
                $financeur->setName($financeurName);
                $this->em->persist($financeur);
                $this->em->flush();
                $this->output->writeln("Financeur créé : {$financeurInitiales}");
            }
            $composter->setFinanceur($financeur);
        }

        // date d'installation
        if (!$cells[1]->isEmpty()) {
            $composter->setDateMiseEnRoute(new DateTime("{$cells[1]->getValue()}-06-26"));
        }

        // Addresse
        $composter->setAddress($cells[8]->getValue());

        // Commune
        $commune = $this->getCommuneByName($cells[5]->getValue());
        if ($commune) {
            $composter->setCommune($commune);
        }

        // Pole
        $poleName = trim($cells[6]->getValue());

        if ('' !== $poleName) {
            if ('LSV' === $poleName || strpos($poleName, 'ignoble')) {
                $poleName = 'Loire, Sèvre et Vignoble';
            }
            $pole = $poleRepository->findOneBy(['name' => $poleName]);

            if (!$pole) {
                $pole = new Pole();
                $pole->setName($poleName);
                $this->em->persist($pole);
                $this->em->flush();
                $this->output->writeln("Pole créé : {$pole->getName()}");
            }
            $composter->setPole($pole);
        }

        // Quartier
        $quartier = $this->getQuartierByName($cells[7]->getValue());
        if ($quartier) {
            $composter->setQuartier($quartier);
        }

        // Volume des pavillons
        $equipement = $this->getEquipement($cells[15]->getValue(), $cells[16]->getValue());
        if ($equipement) {
            $composter->setEquipement($equipement);
        }

        // MC
        $mcName = $cells[17]->getValue();
        if ('' !== $mcName) {
            $mc = $userRepository->findOneBy(['username' => $mcName]);

            if (!$mc) {
                $mc = new User();
                $mc->setUsername($mcName);
                $mc->setEmail(mb_strtolower($mcName).'@compostri.fr');
                $mc->setPlainPassword($mcName);
                $mc->setEnabled(true);
                $mc->setRoles(['ROLE_ADMIN']);
                $this->em->persist($mc);
                $this->em->flush();
                $this->output->writeln("Maitre composter créer : {$mc->getUsername()}");
            }
            $composter->setMc($mc);
        }

        // Lat Long
        $latlong = trim($cells[9]->getValue());
        $latlong = str_replace(PHP_EOL, ' ', $latlong);
        $latlong = str_replace('  ', ' ', $latlong);
        $hasCommat = strpos($latlong, ',');
        $latlong = explode($hasCommat ? ',' : ' ', $latlong);

        if (2 === count($latlong) && is_numeric($latlong[0]) && is_numeric($latlong[1])) {
            $composter->setLat((float) $latlong[0]);
            $composter->setLng((float) $latlong[1]);
        } elseif (count($latlong) > 1) {
            $this->output->writeln("Erreur lors de l‘import de latLong ( composteur #{$name}): ".print_r($latlong, true));
        }

        // Catégorie
        // 11 Copropriété
        // 12 Quartier
        // 13 Jardins
        // 14 Ecole
        if (!empty($cells[11]->getValue())) {
            $composter->setCategorie($categorieRepository->find(2));
        } elseif (!empty($cells[12]->getValue())) {
            $composter->setCategorie($categorieRepository->find(1));
        } elseif (!empty($cells[13]->getValue())) {
            $composter->setCategorie($categorieRepository->find(4));
        } elseif (!empty($cells[14]->getValue())) {
            $composter->setCategorie($categorieRepository->find(3));
        }

        // Référent
        $rName = trim($cells[19]->getValue());
        $rFirstName = trim($cells[20]->getValue());
        $rTel = $cells[21]->getValue();
        $rMail = trim($cells[22]->getValue());
        $rDescription = $cells[23]->getValue();

        $rMail = str_replace(PHP_EOL, ' ', $rMail);
        $rMail = explode(' ', $rMail);
        $rMail = $rMail[0];

        if (!empty($rName) || !empty($rMail)) {
            if (empty($rMail) || !filter_var($rMail, FILTER_VALIDATE_EMAIL)) {
                $rMail = strtolower($rName).'@tobechange.com';
            }

            $newReferent = false;
            $referent = $userRepository->findOneBy(['email' => $rMail]);
            if (!$referent) {
                $referent = new User();
                $referent->setEmail($rMail);
                $newReferent = true;
            }

            if (empty($rName)) {
                $rName = $rMail;
            }

            $referent->setFirstname($rFirstName ?? $rName);
            $referent->setLastname($rName);
            $referent->setUsername($rFirstName ?? $rName);
            $referent->setPlainPassword(random_bytes(24));
            $referent->setPhone($rTel);
            $referent->setRole($rDescription);
            $referent->setEnabled(true);

            $this->em->persist($referent);

            if ($newReferent) {
                $userComposter = new UserComposter();
                $userComposter->setUser($referent);
                $userComposter->setComposter($composter);
                $userComposter->setNewsletter(true);
                $userComposter->setCapability(CapabilityEnumType::REFERENT);

                $this->em->persist($userComposter);
            }
        }

        // Contact
        $syndicLastname = trim($cells[26]->getValue());
        $syndicFirstname = trim($cells[27]->getValue());
        $syndicPhone = trim($cells[28]->getValue());
        $syndicMail = trim($cells[29]->getValue());
        $syndicRole = trim($cells[30]->getValue());

        if (!empty($syndicMail)) {
            $contact = $this->getContactByEmail($syndicMail);

            if ($contact) {
                $contact->setLastName($syndicLastname);
                $contact->setFirstName($syndicFirstname);
                $contact->setPhone($syndicPhone);
                $contact->setRole($syndicRole);
                $contact->setContactType(ContactEnumType::SYNDIC);
                $contact->addComposter($composter);

                $this->em->persist($contact);
            }
        }

        $institutionLastname = trim($cells[31]->getValue());
        $institutionFirstname = trim($cells[32]->getValue());
        $institutionPhone = trim($cells[33]->getValue());
        $institutionMail = trim($cells[34]->getValue());
        $institutionRole = trim($cells[35]->getValue());

        if (!empty($institutionMail)) {
            $contact = $this->getContactByEmail($institutionMail);

            if ($contact) {
                $contact->setLastName($institutionLastname);
                $contact->setFirstName($institutionFirstname);
                $contact->setPhone($institutionPhone);
                $contact->setRole($institutionRole);
                $contact->setContactType(ContactEnumType::INSTITUTION);
                $contact->addComposter($composter);

                $this->em->persist($contact);
            }
        }

        $scolaireLastname = trim($cells[36]->getValue());
        $scolaireFirstname = trim($cells[37]->getValue());
        $scolairePhone = trim($cells[38]->getValue());
        $scolaireMail = trim($cells[39]->getValue());
        $scolaireRole = trim($cells[40]->getValue());

        if (!empty($scolaireMail)) {
            $contact = $this->getContactByEmail($institutionMail);

            if ($contact) {
                $contact->setLastName($scolaireLastname);
                $contact->setFirstName($scolaireFirstname);
                $contact->setPhone($scolairePhone);
                $contact->setRole($scolaireRole);
                $contact->setContactType(ContactEnumType::SCOLAIRE);
                $contact->addComposter($composter);

                $this->em->persist($contact);
            }
        }

        // Persist
        $this->em->persist($composter);
    }

    /**
     * @throws Exception
     */
    private function getDateStringFromFile(Cell $date): ?DateTime
    {
        $dateFormated = null;

        if ($date->isEmpty()) {
            return null;
        }

        if ($date->isDate()) {
            $dateFormated = $date->getValue();
        } else {
            $find = preg_match('/(\d\d\/\d\d\/\d\d\d\d)/', $date->getValue(), $matches);

            if ($find) {
                $dateArray = explode('/', $matches[1]);
                $dateFormated = new DateTime("{$dateArray[2]}-{$dateArray[1]}-{$dateArray[0]}");
            } else {
                $this->output->writeln("Pas un bon format de date : {$date}");
            }
        }

        return $dateFormated;
    }

    private function getComposterByName(string $name): Composter
    {
        $composterRepository = $this->em->getRepository(Composter::class);

        $composter = $composterRepository->findOneBy(['name' => $name]);
        if (!$composter) {
            $composter = new Composter();
            $composter->setName($name);
            $composter->setAddress('');
            $this->output->writeln("Composteur créée'{$name}'");
        }

        return $composter;
    }

    private function getCommuneByName(string $name): ?Commune
    {
        if (empty($name)) {
            return null;
        }

        $communeRepository = $this->em->getRepository(Commune::class);

        $commune = $communeRepository->findOneBy(['name' => $name]);
        if (!$commune) {
            $commune = new Commune();
            $commune->setName($name);
            $this->em->persist($commune);
            $this->em->flush();
            $this->output->writeln("Commune créé '{$name}'");
        }

        return $commune;
    }

    /**
     * @return Quartier
     */
    private function getQuartierByName(string $quartierName): ?Quartier
    {
        if (empty($quartierName)) {
            return null;
        }

        $quartierRepository = $this->em->getRepository(Quartier::class);

        if ('Ile de Nantes' === $quartierName || 'Nantes Ile de Nantes' === $quartierName) {
            $quartierName = 'Nantes Île-de-Nantes';
        } elseif ('Dervallières Zola' === $quartierName) {
            $quartierName = 'Nantes Dervallières-Zola';
        } elseif ('Nantes Malakoff – Saint-Donatien' === $quartierName) {
            $quartierName = 'Nantes Malakoff - Saint-Donatien';
        } elseif ('Breil Barberie' === $quartierName) {
            $quartierName = 'Nantes Breil-Barberie';
        }

        $quartier = $quartierRepository->findOneBy(['name' => $quartierName]);

        if (!$quartier) {
            $quartier = new Quartier();
            $quartier->setName($quartierName);
            $this->em->persist($quartier);
            $this->em->flush();
            $this->output->writeln("Quartier créé : {$quartier->getName()}");
        }

        return $quartier;
    }

    private function getEquipement(string $type, string $capacite): ?Equipement
    {
        if (empty($type) || empty($capacite)) {
            return null;
        }

        $equipementRepository = $this->em->getRepository(Equipement::class);

        $equipement = $equipementRepository->findOneBy(['type' => $type, 'capacite' => $capacite]);

        if (!$equipement) {
            $equipement = new Equipement();
            $equipement->setType($type);
            $equipement->setCapacite($capacite);
            $this->em->persist($equipement);
            $this->em->flush();
            $this->output->writeln("Equipement créée : '{$type}/{$capacite}'");
        }

        return $equipement;
    }

    private function getContactByEmail(string $email): ?Contact
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $contactRepository = $this->em->getRepository(Contact::class);
        $contact = $contactRepository->findOneBy(['email' => $email]);

        if (!$contact) {
            $contact = new Contact();
            $contact->setEmail($email);
        }

        return $contact;
    }
}
