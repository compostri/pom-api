<?php

namespace App\Command;

use App\Entity\Composter;
use App\Entity\MediaObject;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class ImportWordPress extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'compost:import-wordpress';

    private $em;
    private $parameterBag;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        parent::__construct();
        $this->em = $entityManager;
        $this->parameterBag = $parameterBag;
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Import all composter from WordPress CSV')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Import all composter from csv file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'path du fichier CSV a importer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filePath');

        $reader = ReaderEntityFactory::createCSVReader();
        $reader->setFieldDelimiter(';');

        $reader->open($filePath);
        $composterCount = 0;
        $lines = 0;
        foreach ($reader->getSheetIterator() as $key => $sheet) {
            foreach ($sheet->getRowIterator() as $rkey => $row) {
                if ($rkey > 1) {
                    ++$lines;

                    // do stuff with the row
                    $cells = $row->getCells();

                    if (!$cells[0]->isEmpty()) {
                        /**
                         * CSV Format.
                         *
                         * 0 "nom";
                         * 1 "categorie";
                         * 2 "equipement";
                         * 3 "statut";
                         * 4 "lieu";
                         * 5 "date";
                         * 6 "annee";
                         * 7 "lat";
                         * 8 "lon";
                         * 9 "image";
                         */
                        $composterRepository = $this->em->getRepository(Composter::class);

                        $composterName = $this->getRealName($cells[0]->getValue());

                        $composter = $composterRepository->findOneBy(['name' => $composterName]);

                        if (!$composter instanceof Composter) {
                            $output->writeln("{$composterName} : pas trouvé");
                        } else {
                            $imageUrl = $cells[9]->getValue();
                            if ('http://www.compostri.fr/wp-includes/images/media/default.png' !== $imageUrl) {
                                $imageUrl = str_replace('-150x150', '', $imageUrl);
                                $imageName = explode('/', $imageUrl);
                                $imageName = end($imageName);

                                $imagePath = $this->parameterBag->get('upload_destination').$imageName;
                                if ($imageName && !file_exists($imagePath)) {
                                    $output->writeln("import de l‘image {$imageUrl}");
                                    $imageUrl = urlencode($imageUrl);
                                    $imageUrl = str_replace(['%2F', '%3A'], ['/', ':'], $imageUrl);
                                    $content = file_get_contents($imageUrl);

                                    if ($content) {
                                        $fileSize = file_put_contents($imagePath, $content);

                                        if ($fileSize) {
                                            $file = new File($imagePath);

                                            $mediaObject = new MediaObject();
                                            $mediaObject->setFile($file);

                                            $this->em->persist($mediaObject);
                                            $this->em->flush();

                                            $composter->setImage($mediaObject);
                                        }
                                    }
                                }
                            }
                            $composter->setLat((float) $cells[7]->getValue());
                            $composter->setLng((float) $cells[8]->getValue());
                            $this->em->persist($composter);
                            ++$composterCount;
                        }
                    }
                }
            }
        }
        $this->em->flush();
        $output->writeln("{$composterCount} composteurs importé sur {$lines} lignes");
    }

    private function getRealName(string $composterName): string
    {
        $composterName = str_replace(['&rsquo;', '&#8211;'], ['\'', '-'], $composterName);

        $match = [
            'Multi-Accueil Jules Verne' => 'Mutli Accueil Jules Vernes',
            'La Crapaudine' => 'Jardin de la Crapaudine',
            'Jardins Famibio' => 'Jardin Famibio',
            'Ecole La Chauvinière' => 'Ecole Chauvinière',
            'Ecole François Dallet' => 'Ecole François Dallet (primaire)',
            'GB 357' => 'GB357',
            'Chateaubriand Versailles' => 'Versailles Chateaubriand',
            'Mail Picasso' => 'Mail Picasso',
            'Square Gaston Turpin' => 'Gaston Turpin ( Grand T)',
            'Place Similien Guérin' => 'Place du marché',
            'Potager 16 Watt' => 'Potager 16 Watts',
            'La Renaudière' => 'Renaudière',
            'Ecole Emilienne Leroux' => 'Ecole Emilienne Leroux (Maternelle )',
            'Chemin Poisson' => 'Chemin Poisson / ASL Armor',
            'Les Lacs' => 'Résidence Les Lacs',
            'Les Aqueducs - Aux Carrières de Villeneuve' => 'Les Aqueducs',
            'Parc de la Moutonnerie' => 'Moutonnerie',
            'Foyer ERDAM - Perrières' => 'Foyer ERDAM-Perrières',
            'Jardins du Douet' => 'Jardin du Douet',
            'Ça pousse en Amont - Prairie d\'Amont' => 'Ça pousse en amont',
            'Square Jean-Baptiste Terrien' => 'Square Jean Baptiste Terrien',
            'Centre Socio-Culturel Allée Verte' => 'CSC L\'allée verte',
            'Ecole des Réformes' => 'École Réformes',
            'Centre Socio-Culturel Port-au-Blé' => 'CSC Port au Blé',
            'Centre Socio-Culturel Soleil Levant' => 'CSC Soleil Levant',
            'Les jardins de Gaïa' => 'Jardin de Gaia',
            'Compostîle' => 'Compost’île',
            'La Cholière' => 'Cholière',
            'Murillo' => 'Murillo/Les Excuriales',
            'Le Vore\'Koff' => 'Vore-Koff',
            'Ecole Charles Lebourg - Orrion Loquidy' => 'Ecole Charles Lebourg / Orrion Loquidy',
            'Jardin des Noëlles' => 'Jardin des Noelles-Thébaudières',
            'Jardins du Tillay' => 'Jardin du Tillay',
            'Centre Socio-Culturel Henri Normand' => 'CSC Henri Normand',
            'Ecole des mines - Résidence étudiante' => 'Ecole des mines-résidence étudiant',
            'La Ferrière' => 'Cuisine Ecole Ferrière',
            'Ecole George Sand' => 'Ecole George Sand Elémentaire',
            'Ecole Georges Brassens' => 'Ecole Georges Brassens (maternelle)',
            'L\'air du compost' => 'L’air du compost',
            'L\'Allouée' => 'L’Allouée',
            'Val d\'Erdre' => 'Erdre',
            'Coche d\'eau' => 'Coche d’eau',
            'Les Folies de l\'Erdre' => 'Folies de l’Erdre',
            'Compost\'appen' => 'Compo’Stappen',
            'Domaine d\'Esté' => 'Domaine d’Esté',
            'Les Terrasses de l\'Erdre' => 'Terrasses de l’Erdre',
            'Le jardin des Dord\'Oignons' => 'Moulin Lambert - Le jardin des Dord’Oignons',
            'La Frat\'' => 'La Frat’',
            'Le trait d\'oignon' => 'Le Trait d’Oignon',
            'Ingénieurs - La Jonelière' => 'Ingénieurs – Jonelière',
            'Venelle Lamour - Les Forges' => 'Venelles Lamour-Les Forges',
            'FJT Port-Beaulieu - Jard\'îlien' => 'FJT Port beaulieu Jardilien 2',
            'La Closerie de l\'Arche' => 'La Closerie de l’Arche',
        ];

        if (array_key_exists($composterName, $match)) {
            $composterName = $match[$composterName];
        }

        //Compostière Communautaire de Ndenkop : pas trouvé
        //Compostière Communautaire de l'Université de Dschang : pas trouvé
        //Jardin partagé la goutte d'eau : pas trouvé
        //Place du village Le Fort de Saint Jo : pas trouvé
        //Chat'posteur : pas trouvé
        //Anne de Bretagne : pas trouvé
        //Saint Christophe : pas trouvé
        //Le Clos du Perray : pas trouvé
        //60B : pas trouvé
        //Passage de la prise d'eau : pas trouvé
        //Vallon de l'Epinais : pas trouvé
        //Jardins Familiaux de Haute Indre : pas trouvé
        //Jardins Familiaux de Basse Indre : pas trouvé

        //En Projet: Mail Picasso : pas trouvé
        //En projet: Place du village du Trépied : pas trouvé
        //En projet: Portereau : pas trouvé
        //En projet: La Jaguère : pas trouvé

        return $composterName;
    }
}
