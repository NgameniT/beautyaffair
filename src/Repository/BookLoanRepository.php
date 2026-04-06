<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\BookLoan;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookLoan>
 */
class BookLoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookLoan::class);
    }

    public function hasActiveLoan(User $user, Book $book): bool
    {
        return null !== $this->createQueryBuilder('loan')
            ->andWhere('loan.user = :user')
            ->andWhere('loan.book = :book')
            ->andWhere('loan.status = :status')
            ->setParameter('user', $user)
            ->setParameter('book', $book)
            ->setParameter('status', BookLoan::STATUS_ACTIVE)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<BookLoan>
     */
    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('loan')
            ->leftJoin('loan.book', 'book')
            ->addSelect('book')
            ->andWhere('loan.user = :user')
            ->andWhere('loan.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', BookLoan::STATUS_ACTIVE)
            ->orderBy('loan.dueAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('loan')
            ->select('COUNT(loan.id)')
            ->andWhere('loan.status = :status')
            ->setParameter('status', BookLoan::STATUS_ACTIVE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPendingReservations(): int
    {
        return (int) $this->createQueryBuilder('loan')
            ->select('COUNT(loan.id)')
            ->andWhere('loan.status = :status')
            ->andWhere('loan.scheduledFor IS NOT NULL')
            ->andWhere('loan.scheduledFor > :now')
            ->setParameter('status', BookLoan::STATUS_ACTIVE)
            ->setParameter('now', new \DateTimeImmutable('today'))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return list<BookLoan>
     */
    public function findHistory(int $limit = 100): array
    {
        return $this->createQueryBuilder('loan')
            ->leftJoin('loan.user', 'user')
            ->addSelect('user')
            ->leftJoin('loan.book', 'book')
            ->addSelect('book')
            ->orderBy('loan.reservedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
