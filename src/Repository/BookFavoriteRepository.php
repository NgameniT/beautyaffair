<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\BookFavorite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookFavorite>
 */
class BookFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookFavorite::class);
    }

    public function isFavorite(User $user, Book $book): bool
    {
        return null !== $this->createQueryBuilder('favorite')
            ->andWhere('favorite.user = :user')
            ->andWhere('favorite.book = :book')
            ->setParameter('user', $user)
            ->setParameter('book', $book)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<BookFavorite>
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('favorite')
            ->leftJoin('favorite.book', 'book')
            ->addSelect('book')
            ->andWhere('favorite.user = :user')
            ->setParameter('user', $user)
            ->orderBy('favorite.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
