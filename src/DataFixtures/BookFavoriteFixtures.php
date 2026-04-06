<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\BookFavorite;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookFavoriteFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $favorites = [
            [UserFixtures::USER_REFERENCE_PREFIX.'1', BookFixtures::BOOK_REFERENCE_PREFIX.'4'],
            [UserFixtures::USER_REFERENCE_PREFIX.'1', BookFixtures::BOOK_REFERENCE_PREFIX.'11'],
            [UserFixtures::USER_REFERENCE_PREFIX.'2', BookFixtures::BOOK_REFERENCE_PREFIX.'2'],
            [UserFixtures::USER_REFERENCE_PREFIX.'2', BookFixtures::BOOK_REFERENCE_PREFIX.'6'],
            [UserFixtures::USER_REFERENCE_PREFIX.'3', BookFixtures::BOOK_REFERENCE_PREFIX.'8'],
            [UserFixtures::USER_REFERENCE_PREFIX.'4', BookFixtures::BOOK_REFERENCE_PREFIX.'10'],
            [UserFixtures::USER_REFERENCE_PREFIX.'5', BookFixtures::BOOK_REFERENCE_PREFIX.'1'],
            [UserFixtures::USER_REFERENCE_PREFIX.'6', BookFixtures::BOOK_REFERENCE_PREFIX.'12'],
        ];

        foreach ($favorites as [$userRef, $bookRef]) {
            /** @var User $user */
            $user = $this->getReference($userRef, User::class);
            /** @var Book $book */
            $book = $this->getReference($bookRef, Book::class);

            $favorite = (new BookFavorite())
                ->setUser($user)
                ->setBook($book);

            $manager->persist($favorite);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            BookFixtures::class,
        ];
    }
}
