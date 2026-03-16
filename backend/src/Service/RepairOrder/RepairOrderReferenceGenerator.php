<?php

namespace App\Service\RepairOrder;

use App\Entity\BusinessSequence;
use App\Repository\BusinessSequenceRepository;
use Doctrine\ORM\EntityManagerInterface;

class RepairOrderReferenceGenerator
{
    public function __construct(
        private EntityManagerInterface $em,
        private BusinessSequenceRepository $sequenceRepo,
    ) {}

    public function next(): string
    {
        $year = (int) (new \DateTimeImmutable())->format('Y');
        $reference = null;

        $this->em->wrapInTransaction(function () use ($year, &$reference): void {
            $sequence = $this->sequenceRepo->findOneByTypeAndYear('repair_order', $year);

            if (!$sequence) {
                $sequence = new BusinessSequence();
                $sequence->setType('repair_order');
                $sequence->setYear($year);
                $sequence->setLastNumber(0);
                $this->em->persist($sequence);
                $this->em->flush();
            }

            $nextNumber = $sequence->increment();
            $this->em->persist($sequence);
            $this->em->flush();

            $reference = sprintf('SAV-%d-%06d', $year, $nextNumber);
        });

        if ($reference === null) {
            throw new \RuntimeException('Impossible de générer la référence SAV.');
        }

        return $reference;
    }
}
