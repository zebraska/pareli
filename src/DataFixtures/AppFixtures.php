<?php

namespace App\DataFixtures;

use App\Entity\CertificateRequestType;
use App\Entity\Container;
use App\Entity\ContainerQuantity;
use App\Entity\Provider;
use App\Entity\Recycler;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use PhpParser\Node\Expr\Cast\Array_;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private array $cRTs;
    private array $containers;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
        $this->cRTs = [];
        $this->containers = [];
    }

    public function load(ObjectManager $manager): void
    {
        //Destruction certificate types
        $manager->persist($this->createCertificateRequestType('à chaque enlèvement'));
        $manager->persist($this->createCertificateRequestType('annuel'));
        $manager->persist($this->createCertificateRequestType('mail pour envoi du certificat'));

        //Containers types
        $manager->persist($this->createContainer('bacs à roulettes'));
        $manager->persist($this->createContainer('palbox'));
        $manager->persist($this->createContainer('palettes'));
        $manager->persist($this->createContainer('cartons'));

        for ($i = 0; $i < 1000; $i++) {
            $provider = $this->createProvider();
            $manager->persist($provider);
            for ($j = $this->faker->numberBetween(0, 3); $j > 0; $j--) {
                $manager->persist($this->createContainerQuantity($provider));
            }
        }

        for ($i = 0; $i < 1000; $i++) {
            $recycler = $this->createRecycler();
            $manager->persist($recycler);
        }

        $manager->flush();
    }

    public function createCertificateRequestType(String $name): CertificateRequestType
    {
        $cRT = (new CertificateRequestType())->setName($name);
        array_push($this->cRTs, $cRT);
        return ($cRT);
    }

    public function createContainer(String $name): Container
    {
        $container = (new Container())->setName($name);
        array_push($this->containers, $container);
        return $container;
    }

    public function createRecycler(): Recycler
    {
        $recycler = (new Recycler())
            ->setName($this->faker->company())
            ->setAddress($this->faker->streetAddress())
            ->setCity($this->faker->city())
            ->setZipCode($this->faker->postcode());
        return $recycler;
    }

    public function createContainerQuantity(Provider $provider): ContainerQuantity
    {
        return (new ContainerQuantity())
            ->setContainer($this->containers[$this->faker->numberBetween(0, count($this->containers) - 1)])
            ->setQuantity($this->faker->numberBetween(1, 9))
            ->setProvider($provider);
    }

    public function createProvider(): Provider
    {
        $structTypes = ['Entreprise', 'Administration', 'Association', 'Particulier'];
        $attachments = ['Vertou', 'Saint-Nazaire'];
        return (new Provider())
            ->setTypeStruct($structTypes[$this->faker->numberBetween(0, 3)])
            ->setName($this->faker->company())
            ->setAddress($this->faker->streetAddress())
            ->setCity($this->faker->city())
            ->setZipCode($this->faker->postcode())
            ->setAttachment($attachments[$this->faker->numberBetween(0, 1)])
            ->setCommercialContactName($this->faker->name())
            ->setCommercialContactPhone($this->faker->phoneNumber())
            ->setCommercialContactMail($this->faker->companyEmail())
            ->setRemovalContactName($this->faker->name())
            ->setRemovalContactPhone($this->faker->phoneNumber())
            ->setRemovalContactMail($this->faker->companyEmail())
            ->setCertificateContactMail($this->faker->companyEmail())
            ->setIsRegular($this->faker->numberBetween(0, 1))
            ->setComment($this->faker->sentence(500))
            ->setCertificateRequestType($this->cRTs[$this->faker->numberBetween(0, count($this->cRTs) - 1)]);
    }
}
