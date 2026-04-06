<?php

namespace App\Repository;

use App\Entity\LibraryEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LibraryEvent>
 */
class LibraryEventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LibraryEvent::class);
    }

    /**
     * @return list<LibraryEvent>
     */
    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('event')
            ->andWhere('event.startsAt >= :now')
            ->setParameter('now', new \DateTimeImmutable('-1 day'))
            ->orderBy('event.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<LibraryEvent>
     */
    public function findHighlighted(int $limit = 3): array
    {
        return $this->createQueryBuilder('event')
            ->orderBy('event.startsAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
