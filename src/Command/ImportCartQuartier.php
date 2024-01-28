<?php

namespace App\Command;

use App\Entity\Composter;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCartQuartier extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'compost:import-carto-quartier';

    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->em = $entityManager;
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Import all composter from Carto Quartier CSV')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Import all composter from csv file')
            ->addArgument('filePath', InputArgument::REQUIRED, 'path du fichier CSV a importer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filePath = $input->getArgument('filePath');
        $composterRepository = $this->em->getRepository(Composter::class);

        $reader = ReaderEntityFactory::createCSVReader();
        $reader->setFieldDelimiter(',');

        $reader->open($filePath);
        $composterCount = 0;
        $lines = 0;
        foreach ($reader->getSheetIterator() as $key => $sheet) {
            foreach ($sheet->getRowIterator() as $rkey => $row) {
                if ($rkey > 1) {
                    $lines++;

                    // do stuff with the row
                    $cells = $row->getCells();
                    $composter = false;

                    if (! $cells[10]->isEmpty()) {
                        $composterSerialNumber = (int) $cells[10]->getValue();

                        $serailNumberRef = [
                            23  => 243,
                            105 => 97,
                            130 => 120,
                            185 => 186,
                            186 => 187,
                            187 => 188,
                            188 => 189,
                            195 => 196,
                            200 => 201,
                            201 => 202,
                            202 => 203,
                            203 => 204,
                            207 => 208,
                            208 => 209,
                            211 => 212,
                            212 => 213,
                            213 => 214,
                            217 => 65,
                            218 => 219,
                            219 => 220,
                            221 => 222,
                            222 => 223,
                            229 => 230,
                            233 => 234,
                            232 => 233,
                        ];

                        if (array_key_exists($composterSerialNumber, $serailNumberRef)) {
                            $composterSerialNumber = $serailNumberRef[ $composterSerialNumber ];
                        }

                        $composter = $composterRepository->findOneBy([ 'serialNumber' => $composterSerialNumber ]);
                    } elseif (! $cells[1]->isEmpty()) {
                        $name = str_replace('Composteur ', '', $cells[1]->getValue());
                        switch ($name) {
                            case 'Place de village Espace du Fort':
                                $name = 'Place de Village Le Fort';
                                break;
                            case 'Trait d\'oignon':
                                $name = 'Le Trait d\'Oignon';
                                break;
                            case 'de la Cholière':
                                $name = 'Cholière';
                                break;
                            case 'Val du Cens':
                                $name = 'Val de Cens';
                                break;
                        }
                        $composter = $composterRepository->findOneBy([ 'name' => $name ]);
                    }

                    if (! $composter instanceof  Composter) {
                        $output->writeln("{$cells[1]->getValue()} : pas trouvé");
                    } else {
                        $composter->setPublicDescription($cells[2]->getValue());
                        $composter->setPermanencesDescription($cells[8]->getValue());
                        $this->em->persist($composter);
                        $composterCount++;
                    }
                }
            }
        }
        $this->em->flush();
        $output->writeln("{$composterCount} composteurs importé sur {$lines} lignes");
    }
}
