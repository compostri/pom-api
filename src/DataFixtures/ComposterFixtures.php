<?php

namespace App\DataFixtures;

use App\DBAL\Types\BroyatEnumType;
use App\DBAL\Types\StatusEnumType;
use App\Entity\Composter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ComposterFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $composter = new Composter();
            $composter->setCommune(
                $this->getReference( 'commune_' . $this->faker->numberBetween(0, 19) )
            );
            $composter->setCategorie(
                $this->faker->boolean(80) ?
                    $this->getReference( 'categorie_' . $this->faker->numberBetween(0, 4) )
                    :
                    null
            );
            $composter->setApprovisionnementBroyat(
                $this->faker->boolean(80) ?
                    $this->getReference( 'approvisionnement_broyat_' . $this->faker->numberBetween(0, 5) )
                    :
                    null
            );
            $composter->setPole(
                $this->faker->boolean(80) ?
                    $this->getReference( 'pole_' . $this->faker->numberBetween(0, 6) )
                    :
                    null
            );
            $composter->setQuartier(
                $this->faker->boolean(80) ?
                    $this->getReference( 'quartier_' . $this->faker->numberBetween(0, 16) )
                    :
                    null
            );
            $composter->setEquipement(
                $this->faker->boolean(80) ?
                    $this->getReference( 'equipement_' . $this->faker->numberBetween(0, 14) )
                    :
                    null
            );
            $composter->setMc(
                $this->faker->boolean(90) ?
                    $this->getReference( 'mc_' . $this->faker->numberBetween(0, 9) )
                    :
                    null
            );
            $composter->setName($this->faker->name);
            $composter->setAddress($this->faker->address);

            // On prévoit 10% de composteur sans coordonnée
            if($this->faker->boolean(90)){
                $composter->setLat($this->faker->randomFloat(4, 47.1890, 47.3099));
                $composter->setLng($this->faker->randomFloat(4, -1.4294, -1.5704));
            }

            $composter->setDescription( $this->faker->boolean(80) ? $this->faker->paragraph : null );
            $composter->setPermanencesDescription( $this->faker->boolean(80) ? $this->faker->paragraph : null );
            $composter->setAcceptNewMembers( $this->faker->boolean);
            $composter->setBroyatLevel( $this->faker->randomElement(BroyatEnumType::getChoices()));
            $composter->setAlimentsAutorises($this->faker->boolean ? $this->faker->paragraph : null);
            $composter->setAlimentsNonAutorises($this->faker->boolean ? $this->faker->paragraph : null);
            $composter->setMailjetListID($this->faker->randomNumber(5));
            $composter->setStatus( $this->faker->randomElement(StatusEnumType::getChoices()));

            $installationDate = $this->faker->dateTimeBetween('2007-01-01');
            $inaugurationDate = $this->faker->dateTimeBetween($installationDate);
            $composter->setDateInstallation($installationDate);
            $composter->setDateInauguration($inaugurationDate);
            $composter->setDateMiseEnRoute($this->faker->dateTimeBetween($inaugurationDate));

            $composter->setPermanencesDescription($this->faker->paragraph);
            $composter->setPermanencesRule($this->faker->paragraphs(3, true));
            $composter->setPublicDescription($this->faker->paragraphs(3 , true));
            $composter->setSerialNumber($i + 1);

            $composter->setSignaletiqueRond($this->faker->boolean);
            $composter->setSignaletiquePanneau($this->faker->boolean);
            $composter->setHasCroc($this->faker->boolean);
            $composter->setHasCadenas($this->faker->boolean);
            $composter->setHasFourche($this->faker->boolean);
            $composter->setHasThermometre($this->faker->boolean);
            $composter->setHasPeson($this->faker->boolean);
            $composter->setPlateNumber($this->faker->boolean ? $this->faker->numerify('##-###-##') : null);

            $nbInscrit = $this->faker->numberBetween(0, 100);
            $composter->setNbInscrit($this->faker->boolean ? $nbInscrit : null);
            $composter->setNbDeposant($this->faker->boolean ? $this->faker->numberBetween(0, $nbInscrit) : null);
            $composter->setNbFoyersPotentiels($this->faker->boolean ? $this->faker->numberBetween($nbInscrit, 150) : null);

            $composter->setFinanceurSuivi($this->faker->boolean ? $this->getReference('financeur_' . $this->faker->numberBetween(0, 9)) : null);
            $composter->setFinanceur($this->faker->boolean ? $this->getReference('financeur_' . $this->faker->numberBetween(0, 9)) : null);

            $manager->persist($composter);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ApprovisionnementBroyatFixtures::class,
            CommuneFixtures::class,
            CategorieFixtures::class,
            PoleFixtures::class,
            QuartierFixtures::class,
            EquipementFixtures::class,
            UserFixtures::class,
            FinanceurFixtures::class,
        ];
    }
}
