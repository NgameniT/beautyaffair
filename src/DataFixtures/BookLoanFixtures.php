<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\BookLoan;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookLoanFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $activeLoans = [
            [UserFixtures::USER_REFERENCE_PREFIX.'1', BookFixtures::BOOK_REFERENCE_PREFIX.'1', null],
            [UserFixtures::USER_REFERENCE_PREFIX.'2', BookFixtures::BOOK_REFERENCE_PREFIX.'4', null],
            [UserFixtures::USER_REFERENCE_PREFIX.'3', BookFixtures::BOOK_REFERENCE_PREFIX.'6', new \DateTimeImmutable('+2 days')],
            [UserFixtures::USER_REFERENCE_PREFIX.'4', BookFixtures::BOOK_REFERENCE_PREFIX.'11', null],
        ];

        foreach ($activeLoans as [$userRef, $bookRef, $scheduledFor]) {
            /** @var User $user */
            $user = $this->getReference($userRef, User::class);
            /** @var Book $book */
            $book = $this->getReference($bookRef, Book::class);

            $loan = (new BookLoan())
                ->setUser($user)
                ->setBook($book)
                ->setDueAt(new \DateTimeImmutable('+21 days'));

            if ($scheduledFor instanceof \DateTimeImmutable) {
                $loan->setScheduledFor($scheduledFor);
            }

            $manager->persist($loan);
        }

        $returnedLoans = [
            [UserFixtures::USER_REFERENCE_PREFIX.'1', BookFixtures::BOOK_REFERENCE_PREFIX.'2'],
            [UserFixtures::USER_REFERENCE_PREFIX.'5', BookFixtures::BOOK_REFERENCE_PREFIX.'8'],
            [UserFixtures::USER_REFERENCE_PREFIX.'6', BookFixtures::BOOK_REFERENCE_PREFIX.'9'],
        ];

        foreach ($returnedLoans as [$userRef, $bookRef]) {
            /** @var User $user */
            $user = $this->getReference($userRef, User::class);
            /** @var Book $book */
            $book = $this->getReference($bookRef, Book::class);

            $loan = (new BookLoan())
                ->setUser($user)
                ->setBook($book)
                ->setDueAt(new \DateTimeImmutable('-5 days'));

            $loan->markAsReturned();
            $manager->persist($loan);
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
