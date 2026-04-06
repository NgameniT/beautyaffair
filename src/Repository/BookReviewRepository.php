<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\BookReview;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookReview>
 */
class BookReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookReview::class);
    }

    /**
     * @return list<BookReview>
     */
    public function findApprovedByBook(Book $book): array
    {
        return $this->createQueryBuilder('review')
            ->leftJoin('review.user', 'user')
            ->addSelect('user')
            ->andWhere('review.book = :book')
            ->andWhere('review.isApproved = :approved')
            ->setParameter('book', $book)
            ->setParameter('approved', true)
            ->orderBy('review.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUserAndBook(User $user, Book $book): ?BookReview
    {
        return $this->createQueryBuilder('review')
            ->andWhere('review.user = :user')
            ->andWhere('review.book = :book')
            ->setParameter('user', $user)
            ->setParameter('book', $book)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function averageRating(Book $book): float
    {
        $value = $this->createQueryBuilder('review')
            ->select('AVG(review.rating)')
            ->andWhere('review.book = :book')
            ->andWhere('review.isApproved = :approved')
            ->setParameter('book', $book)
            ->setParameter('approved', true)
            ->getQuery()
            ->getSingleScalarResult();

        return null === $value ? 0.0 : round((float) $value, 1);
    }

    /**
     * @return list<BookReview>
     */
    public function findPendingModeration(): array
    {
        return $this->createQueryBuilder('review')
            ->leftJoin('review.book', 'book')
            ->addSelect('book')
            ->leftJoin('review.user', 'user')
            ->addSelect('user')
            ->andWhere('review.isApproved = :approved')
            ->setParameter('approved', false)
            ->orderBy('review.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
