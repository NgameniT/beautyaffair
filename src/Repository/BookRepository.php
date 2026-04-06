<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return list<Book>
     */
    public function findForCatalogue(?string $query, ?string $category): array
    {
        $builder = $this->createQueryBuilder('book')
            ->orderBy('book.featured', 'DESC')
            ->addOrderBy('book.title', 'ASC');

        if ($query) {
            $builder
                ->andWhere('LOWER(book.title) LIKE :query OR LOWER(book.author) LIKE :query OR LOWER(book.summary) LIKE :query')
                ->setParameter('query', '%'.mb_strtolower($query).'%');
        }

        if ($category) {
            $builder
                ->andWhere('book.category = :category')
                ->setParameter('category', $category);
        }

        return $builder->getQuery()->getResult();
    }

    /**
     * @return list<string>
     */
    public function findCategories(): array
    {
        $rows = $this->createQueryBuilder('book')
            ->select('DISTINCT book.category AS category')
            ->orderBy('book.category', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): string => $row['category'], $rows);
    }

    /**
     * @return list<Book>
     */
    public function findFeatured(int $limit = 3): array
    {
        return $this->createQueryBuilder('book')
            ->andWhere('book.featured = true')
            ->orderBy('book.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
