<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ClientFixtures extends Fixture
{
    public const COUNT = 50;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $client1 = (new Client())
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPhone('0600000001')
            ->setEmail('john@example.com')
            ->setAddress('12 Rue de Rivoli')
            ->setPostalCode('75001')
            ->setCity('Paris')
            ->setLandlinePhone('0140203040')
            ->setIsAnonymized(false)
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($client1);
        $this->addReference('client.1', $client1);
        $this->addReference('client.dynamic_1', $client1);

        $client2 = (new Client())
            ->setFirstName('Jane')
            ->setLastName('Smith')
            ->setPhone('0600000002')
            ->setEmail('jane@example.com')
            ->setAddress('8 Avenue Jean Jaurès')
            ->setPostalCode('69007')
            ->setCity('Lyon')
            ->setLandlinePhone('0472121212')
            ->setIsAnonymized(false)
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($client2);
        $this->addReference('client.2', $client2);
        $this->addReference('client.dynamic_2', $client2);

        $clientAnon = (new Client())
            ->setFirstName('Anonyme')
            ->setLastName('Anonyme')
            ->setPhone('ANON-test-client')
            ->setEmail(null)
            ->setAddress(null)
            ->setPostalCode(null)
            ->setCity(null)
            ->setLandlinePhone(null)
            ->setIsAnonymized(true)
            ->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($clientAnon);
        $this->addReference('client.anonymized', $clientAnon);
        $this->addReference('client.dynamic_3', $clientAnon);

        for ($i = 4; $i <= self::COUNT; $i++) {
            $client = new Client();
            $client
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setPhone($this->fakeFrenchMobile($i))
                ->setEmail($faker->optional(0.85)->safeEmail())
                ->setAddress($faker->streetAddress())
                ->setPostalCode((string) $faker->numberBetween(1000, 95880))
                ->setCity($faker->city())
                ->setLandlinePhone($faker->optional(0.6)->numerify('0#########'))
                ->setIsAnonymized(false)
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($client);
            $this->addReference('client.dynamic_' . $i, $client);
        }

        $manager->flush();
    }

    private function fakeFrenchMobile(int $i): string
    {
        return '06' . str_pad((string) $i, 8, '0', STR_PAD_LEFT);
    }
}