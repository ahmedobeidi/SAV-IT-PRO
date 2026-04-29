<?php

namespace App\DataFixtures;

use App\Entity\BusinessSequence;
use App\Entity\Client;
use App\Entity\EquipmentModel;
use App\Entity\Issue;
use App\Entity\RepairOrder;
use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\RepairOrderStatus;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RepairOrderFixtures extends Fixture implements DependentFixtureInterface
{
    public const COUNT = 50;

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $year = (int) (new \DateTimeImmutable())->format('Y');
        $maxNumber = 0;

        /** @var User $reception */
        $reception = $this->getReference('user.reception', User::class);

        /** @var User $tech */
        $tech = $this->getReference('user.tech', User::class);

        $repair1 = (new RepairOrder())
            ->setReference(sprintf('SAV-%d-000001', $year))
            ->setCreatedBy($reception)
            ->setCreatedFor($this->getReference('client.1', Client::class))
            ->setEquipmentModel($this->getReference('equipment_model.iphone13', EquipmentModel::class))
            ->setIssue($this->getReference('issue.screen', Issue::class))
            ->setAssignedTo($tech)
            ->setPrice(120)
            ->setDeposit(20)
            ->setDescription('Remplacement de l’écran nécessaire')
            ->setStatus(RepairOrderStatus::IN_PROGRESS)
            ->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($repair1);
        $this->addReference('repair_order.1', $repair1);
        $maxNumber = 1;

        $ticket = (new Ticket())
            ->setRepairOrder($repair1)
            ->setGeneratedBy($reception)
            ->setStoragePath('var/storage/tickets/test/test-ticket.pdf')
            ->setFilename(sprintf('ticket-SAV-%d-000001.pdf', $year))
            ->setMimeType('application/pdf')
            ->setSize(1234)
            ->setSnapshot([
                'reference' => sprintf('SAV-%d-000001', $year),
                'status' => 'IN_PROGRESS',
            ])
            ->setSnapshotHash(hash('sha256', 'fixture-ticket'))
            ->setGeneratedAt(new \DateTimeImmutable());

        $manager->persist($ticket);
        $this->addReference('ticket.1', $ticket);

        $statuses = [
            RepairOrderStatus::CREATED,
            RepairOrderStatus::IN_PROGRESS,
            RepairOrderStatus::WAITING_PARTS,
            RepairOrderStatus::DONE,
            RepairOrderStatus::DELIVERED,
            RepairOrderStatus::CANCELED,
        ];

        for ($i = 2; $i <= self::COUNT; $i++) {
            /** @var Client $client */
            $client = $this->getReference('client.dynamic_' . (($i % 49) + 1), Client::class);

            /** @var EquipmentModel $model */
            $model = $this->getReference('equipment_model.dynamic_' . ((($i - 1) % 50) + 1), EquipmentModel::class);

            $modelType = $model->getEquipmentBrand()->getEquipmentType();

            $issue = null;
            for ($j = 1; $j <= 50; $j++) {
                /** @var Issue $candidate */
                $candidate = $this->getReference('issue.dynamic_' . $j, Issue::class);
                if ($candidate->getEquipmentType()->getId() === $modelType->getId()) {
                    $issue = $candidate;
                    break;
                }
            }

            if (!$issue) {
                continue;
            }

            /** @var User $creator */
            $creator = $this->getReference('user.dynamic_' . $faker->numberBetween(2, 15), User::class);

            $assignedTo = null;
            if ($faker->boolean(55)) {
                for ($u = 1; $u <= 50; $u++) {
                    /** @var User $candidateUser */
                    $candidateUser = $this->getReference('user.dynamic_' . $u, User::class);
                    if ($candidateUser->getRole() === UserRole::TECHNICIAN) {
                        $assignedTo = $candidateUser;
                        break;
                    }
                }
            }

            $repair = (new RepairOrder())
                ->setReference(sprintf('SAV-%d-%06d', $year, $i))
                ->setCreatedBy($creator)
                ->setCreatedFor($client)
                ->setEquipmentModel($model)
                ->setIssue($issue)
                ->setAssignedTo($assignedTo)
                ->setPrice((float) $faker->numberBetween(49, 899))
                ->setDeposit($faker->boolean(70) ? (float) $faker->numberBetween(0, 250) : null)
                ->setDescription($faker->sentence(12))
                ->setStatus($faker->randomElement($statuses))
                ->setUpdatedAt(new \DateTimeImmutable());

            $manager->persist($repair);
            $this->addReference('repair_order.dynamic_' . $i, $repair);
            $maxNumber = $i;
        }

        $sequence = (new BusinessSequence())
            ->setType('repair_order')
            ->setYear($year)
            ->setLastNumber($maxNumber);

        $manager->persist($sequence);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ClientFixtures::class,
            EquipmentFixtures::class,
        ];
    }
}