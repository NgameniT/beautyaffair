<?php

namespace App\Repository;

use App\Entity\EventRegistration;
use App\Entity\LibraryEvent;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventRegistration>
 */
class EventRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventRegistration::class);
    }

    public function isRegistered(User $user, LibraryEvent $event): bool
    {
        return null !== $this->createQueryBuilder('registration')
            ->andWhere('registration.user = :user')
            ->andWhere('registration.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countForEvent(LibraryEvent $event): int
    {
        return (int) $this->createQueryBuilder('registration')
            ->select('COUNT(registration.id)')
            ->andWhere('registration.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<EventRegistration>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('registration')
            ->leftJoin('registration.event', 'event')
            ->addSelect('event')
            ->andWhere('registration.user = :user')
            ->setParameter('user', $user)
            ->orderBy('event.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
