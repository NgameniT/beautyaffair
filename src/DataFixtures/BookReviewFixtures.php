<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\BookReview;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $reviews = [
            [UserFixtures::USER_REFERENCE_PREFIX.'1', BookFixtures::BOOK_REFERENCE_PREFIX.'1', 5, 'Lecture tres inspirante, un classique qui touche a chaque relecture.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'2', BookFixtures::BOOK_REFERENCE_PREFIX.'1', 4, 'Texte court mais profond, parfait pour un club lecture.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'3', BookFixtures::BOOK_REFERENCE_PREFIX.'2', 5, 'Toujours actuel et puissant sur le plan politique.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'4', BookFixtures::BOOK_REFERENCE_PREFIX.'4', 5, 'Univers tres riche, personnages memorables et intrigue solide.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'5', BookFixtures::BOOK_REFERENCE_PREFIX.'6', 4, 'Une excellente entree dans la science-fiction classique.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'6', BookFixtures::BOOK_REFERENCE_PREFIX.'8', 4, 'Roman elegant, ironique et tres agreable a lire.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'7', BookFixtures::BOOK_REFERENCE_PREFIX.'9', 3, 'Bonne enquete, ambiance sombre bien construite.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'8', BookFixtures::BOOK_REFERENCE_PREFIX.'10', 4, 'Biographie detaillee et tres documentee.', true],
            [UserFixtures::USER_REFERENCE_PREFIX.'1', BookFixtures::BOOK_REFERENCE_PREFIX.'11', 5, 'Construction du monde remarquable, tres bonne surprise.', false],
            [UserFixtures::USER_REFERENCE_PREFIX.'2', BookFixtures::BOOK_REFERENCE_PREFIX.'12', 4, 'Classique vivant et surprenant par son humour.', false],
        ];

        foreach ($reviews as [$userRef, $bookRef, $rating, $comment, $isApproved]) {
            /** @var User $user */
            $user = $this->getReference($userRef, User::class);
            /** @var Book $book */
            $book = $this->getReference($bookRef, Book::class);

            $review = (new BookReview())
                ->setUser($user)
                ->setBook($book)
                ->setRating($rating)
                ->setComment($comment)
                ->setIsApproved($isApproved);

            $manager->persist($review);
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
