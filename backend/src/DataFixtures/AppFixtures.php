<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\EquipmentType;
use App\Entity\EquipmentBrand;
use App\Entity\EquipmentModel;
use App\Entity\Issue;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $equipmentTypes = [];
        $equipmentBrands = [];

        /*
        |--------------------------------------------------------------------------
        | USERS - 100
        |--------------------------------------------------------------------------
        | 1 SUPER_ADMIN
        | admins: admin{n}@itpro.com
        | technicians: tech{n}@itpro.com
        | reception: user{n}@itpro.com
        |--------------------------------------------------------------------------
        */

        // 1 super admin
        $superAdmin = new User();
        $superAdmin
            ->setFirstName('Super')
            ->setLastName('Admin')
            ->setEmail('super@itpro.com')
            ->setRole(UserRole::SUPER_ADMIN)
            ->setIsActive(true)
            ->setIsAnonymized(false)
            ->setPasswordSetupRequired(false)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $superAdmin->setPassword(
            $this->passwordHasher->hashPassword($superAdmin, 'Super123!')
        );

        $manager->persist($superAdmin);

        $adminCount = 0;
        $techCount = 0;
        $receptionCount = 0;

        for ($i = 2; $i <= 100; $i++) {
            $user = new User();

            // distribute roles among ADMIN / TECHNICIAN / RECEPTION
            $role = match (($i - 2) % 3) {
                0 => UserRole::ADMIN,
                1 => UserRole::TECHNICIAN,
                default => UserRole::RECEPTION,
            };

            switch ($role) {
                case UserRole::ADMIN:
                    $adminCount++;
                    $email = sprintf('admin%d@itpro.com', $adminCount);
                    $plainPassword = 'Admin123!';
                    break;

                case UserRole::TECHNICIAN:
                    $techCount++;
                    $email = sprintf('tech%d@itpro.com', $techCount);
                    $plainPassword = 'Tech123!';
                    break;

                case UserRole::RECEPTION:
                    $receptionCount++;
                    $email = sprintf('recep%d@itpro.com', $receptionCount);
                    $plainPassword = 'Recep123!';
                    break;

                default:
                    $email = $faker->unique()->safeEmail();
                    $plainPassword = 'Password123!';
                    break;
            }

            $user
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($email)
                ->setRole($role)
                ->setIsActive($faker->boolean(90))
                ->setIsAnonymized(false)
                ->setPasswordSetupRequired(false)
                ->setCreatedAt(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-2 years', 'now')
                ))
                ->setUpdatedAt(
                    $faker->boolean(70)
                        ? \DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween('-1 year', 'now')
                        )
                        : null
                );

            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plainPassword)
            );

            $manager->persist($user);
        }

        /*
        |--------------------------------------------------------------------------
        | CLIENTS - 100
        |--------------------------------------------------------------------------
        | email format: clientname@itpro.com
        | not all clients have email
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 100; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();

            $email = null;
            if ($faker->boolean(70)) {
                $normalizedFirstName = $this->normalizeForEmail($firstName);
                $normalizedLastName = $this->normalizeForEmail($lastName);

                $email = sprintf(
                    '%s.%s%d@itpro.com',
                    $normalizedFirstName,
                    $normalizedLastName,
                    $i
                );
            }

            $client = new Client();
            $client
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setPhone('06' . $faker->numerify('########'))
                ->setEmail($email)
                ->setAddress($faker->boolean(80) ? $faker->address() : null)
                ->setPostalCode($faker->boolean(80) ? $faker->postcode() : null)
                ->setCity($faker->boolean(80) ? $faker->city() : null)
                ->setLandlinePhone($faker->boolean(40) ? '01' . $faker->numerify('########') : null)
                ->setIsAnonymized(false)
                ->setCreatedAt(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-2 years', 'now')
                ))
                ->setUpdatedAt(
                    $faker->boolean(70)
                        ? \DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween('-1 year', 'now')
                        )
                        : null
                );

            $manager->persist($client);
        }

        /*
        |--------------------------------------------------------------------------
        | EQUIPMENT TYPES - 100
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i <= 100; $i++) {
            $type = new EquipmentType();
            $type
                ->setName(sprintf('Type %03d', $i))
                ->setCreatedAt(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-2 years', 'now')
                ))
                ->setUpdatedAt(
                    $faker->boolean(70)
                        ? \DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween('-1 year', 'now')
                        )
                        : null
                );

            $manager->persist($type);
            $equipmentTypes[] = $type;
        }

        /*
        |--------------------------------------------------------------------------
        | EQUIPMENT BRANDS - 100
        |--------------------------------------------------------------------------
        */
        foreach ($equipmentTypes as $i => $type) {
            $brand = new EquipmentBrand();
            $brand
                ->setName(sprintf('Brand %d', $i + 1))
                ->setEquipmentType($type)
                ->setCreatedAt(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-2 years', 'now')
                ))
                ->setUpdatedAt(
                    $faker->boolean(70)
                        ? \DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween('-1 year', 'now')
                        )
                        : null
                );

            $manager->persist($brand);
            $equipmentBrands[] = $brand;
        }

        /*
        |--------------------------------------------------------------------------
        | EQUIPMENT MODELS - 100
        |--------------------------------------------------------------------------
        */
        foreach ($equipmentBrands as $i => $brand) {
            $model = new EquipmentModel();
            $model
                ->setName(sprintf('Model %d', $i + 1))
                ->setEquipmentBrand($brand)
                ->setCreatedAt(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-2 years', 'now')
                ))
                ->setUpdatedAt(
                    $faker->boolean(70)
                        ? \DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween('-1 year', 'now')
                        )
                        : null
                );

            $manager->persist($model);
        }

        /*
        |--------------------------------------------------------------------------
        | ISSUES - 100
        |--------------------------------------------------------------------------
        */
        $issueNames = [
            'Écran cassé',
            'Batterie défectueuse',
            'Ne s’allume plus',
            'Surchauffe',
            'Connecteur de charge endommagé',
            'Clavier HS',
            'Carte mère défectueuse',
            'Bruit anormal',
            'Problème de démarrage',
            'Écran noir',
            'Lent',
            'Port USB HS',
            'Wi-Fi ne fonctionne pas',
            'Bluetooth défaillant',
            'Ventilateur bruyant',
            'Caméra non détectée',
            'Microphone HS',
            'Haut-parleur défectueux',
            'Disque dur endommagé',
            'SSD non reconnu',
        ];

        for ($i = 1; $i <= 100; $i++) {
            $issue = new Issue();
            $issue
                ->setName($issueNames[array_rand($issueNames)] . ' #' . $i)
                ->setEquipmentType($equipmentTypes[array_rand($equipmentTypes)])
                ->setCreatedAt(\DateTimeImmutable::createFromMutable(
                    $faker->dateTimeBetween('-2 years', 'now')
                ))
                ->setUpdatedAt(
                    $faker->boolean(70)
                        ? \DateTimeImmutable::createFromMutable(
                            $faker->dateTimeBetween('-1 year', 'now')
                        )
                        : null
                );

            $manager->persist($issue);
        }

        $manager->flush();
    }

    private function normalizeForEmail(string $value): string
    {
        $value = mb_strtolower($value);

        $replacements = [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
            '\'' => '',
            ' ' => '.',
            '-' => '.',
        ];

        $value = strtr($value, $replacements);
        $value = preg_replace('/[^a-z0-9.]/', '', $value) ?? '';
        $value = preg_replace('/\.+/', '.', $value) ?? '';
        $value = trim($value, '.');

        return $value;
    }
}
