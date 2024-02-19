<?php

namespace App\Command;

use DirectoryIterator;
use Intervention\Image\ImageManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageResize extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'compost:image-resize';
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        parent::__construct();
        $this->parameterBag = $parameterBag;
    }

    protected function configure()
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Images resize')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Images resize')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // create an image manager instance with favored driver
        $manager = new ImageManager(['driver' => 'imagick']);

        // to finally create image instances
        $upload_destination = $this->parameterBag->get('upload_destination');
        foreach (new DirectoryIterator($upload_destination) as $file) {
            if ($file->isDot() || '.DS_Store' === $file->getFilename()) {
                continue;
            }

            $fileName = $file->getFilename();

            $output->writeln("On traite l’image {$fileName}");

            $manager->make($upload_destination.$fileName)
                ->widen(942, function ($constraint) {
                    $constraint->upsize();
                })
                ->save($upload_destination.$fileName);
        }
    }
}
