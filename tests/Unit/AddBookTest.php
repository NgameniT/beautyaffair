<?php

namespace App\Tests\Unit;

use App\Entity\Book;
use PHPUnit\Framework\TestCase;

final class AddBookTest extends TestCase
{
    public function testBookCanBeCreatedWithRequiredFields(): void
    {
        $book = (new Book())
            ->setTitle('Clean Architecture')
            ->setAuthor('Robert C. Martin')
            ->setCategory('Informatique')
            ->setLanguage('Francais')
            ->setSummary('Livre de reference sur l\'architecture logicielle.')
            ->setCoverTheme('ocean')
            ->setPublishedYear(2018)
            ->setPageCount(460)
            ->setAvailableCopies(8)
            ->setSlug('clean-architecture')
            ->setFeatured(true);

        self::assertSame('Clean Architecture', $book->getTitle());
        self::assertSame('Informatique', $book->getCategory());
        self::assertSame('Francais', $book->getLanguage());
        self::assertSame(8, $book->getAvailableCopies());
    }
}
