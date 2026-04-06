<?php

namespace App\DataFixtures;

use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{
    public const BOOK_REFERENCE_PREFIX = 'book_';

    public function load(ObjectManager $manager): void
    {
        $books = [
            [
                'title' => 'Le Petit Prince',
                'author' => 'Antoine de Saint-Exupery',
                'category' => 'Litterature',
                'language' => 'Francais',
                'summary' => 'Un conte poetique qui explore l amitie, l enfance et le sens de la vie a travers les yeux d un petit voyageur.',
                'coverTheme' => 'yellow',
                'publishedYear' => 1943,
                'pageCount' => 96,
                'availableCopies' => 4,
                'featured' => true,
                'slug' => 'le-petit-prince',
            ],
            [
                'title' => '1984',
                'author' => 'George Orwell',
                'category' => 'Dystopie',
                'language' => 'Anglais',
                'summary' => 'Un roman d anticipation sur la surveillance totale, la manipulation du langage et la resistance individuelle face au pouvoir.',
                'coverTheme' => 'dark',
                'publishedYear' => 1949,
                'pageCount' => 328,
                'availableCopies' => 3,
                'featured' => true,
                'slug' => '1984-orwell',
            ],
            [
                'title' => 'Les Miserables',
                'author' => 'Victor Hugo',
                'category' => 'Classique',
                'language' => 'Francais',
                'summary' => 'Une fresque sociale qui suit des destins croises et questionne la justice, la redemption et la dignite humaine.',
                'coverTheme' => 'blue',
                'publishedYear' => 1862,
                'pageCount' => 1463,
                'availableCopies' => 2,
                'featured' => false,
                'slug' => 'les-miserables',
            ],
            [
                'title' => 'Le Seigneur des Anneaux',
                'author' => 'J. R. R. Tolkien',
                'category' => 'Fantastique',
                'language' => 'Francais',
                'summary' => 'Une epopee de fantasy autour d une quete perilleuse pour detruire un anneau et sauver la Terre du Milieu.',
                'coverTheme' => 'green',
                'publishedYear' => 1954,
                'pageCount' => 1216,
                'availableCopies' => 5,
                'featured' => true,
                'slug' => 'seigneur-des-anneaux',
            ],
            [
                'title' => 'Meditations',
                'author' => 'Marc Aurele',
                'category' => 'Philosophie',
                'language' => 'Francais',
                'summary' => 'Des pensees stoiciennes sur la maitrise de soi, le devoir et la serenite dans les epreuves du quotidien.',
                'coverTheme' => 'teal',
                'publishedYear' => 180,
                'pageCount' => 320,
                'availableCopies' => 4,
                'featured' => false,
                'slug' => 'meditations-marc-aurele',
            ],
            [
                'title' => 'Dune',
                'author' => 'Frank Herbert',
                'category' => 'Science-Fiction',
                'language' => 'Anglais',
                'summary' => 'Une saga de science-fiction politique et ecologique sur une planete desertique cruciale pour l avenir de l univers.',
                'coverTheme' => 'orange',
                'publishedYear' => 1965,
                'pageCount' => 544,
                'availableCopies' => 6,
                'featured' => true,
                'slug' => 'dune',
            ],
            [
                'title' => 'L Ile au Tresor',
                'author' => 'Robert Louis Stevenson',
                'category' => 'Aventure',
                'language' => 'Francais',
                'summary' => 'Un recit d aventure maritime rythme par les cartes secretes, les mutineries et la quete d un tresor legendaire.',
                'coverTheme' => 'red',
                'publishedYear' => 1883,
                'pageCount' => 280,
                'availableCopies' => 3,
                'featured' => false,
                'slug' => 'ile-au-tresor',
            ],
            [
                'title' => 'Orgueil et Prejuges',
                'author' => 'Jane Austen',
                'category' => 'Romance',
                'language' => 'Anglais',
                'summary' => 'Un classique de la romance qui observe avec finesse les conventions sociales et les sentiments au sein de la gentry anglaise.',
                'coverTheme' => 'purple',
                'publishedYear' => 1813,
                'pageCount' => 432,
                'availableCopies' => 4,
                'featured' => false,
                'slug' => 'orgueil-et-prejuges',
            ],
            [
                'title' => 'Le Chien des Baskerville',
                'author' => 'Arthur Conan Doyle',
                'category' => 'Policier',
                'language' => 'Francais',
                'summary' => 'Une enquete de Sherlock Holmes melant mystere, superstition et deduction dans les landes anglaises.',
                'coverTheme' => 'dark',
                'publishedYear' => 1902,
                'pageCount' => 256,
                'availableCopies' => 3,
                'featured' => false,
                'slug' => 'chien-des-baskerville',
            ],
            [
                'title' => 'Steve Jobs',
                'author' => 'Walter Isaacson',
                'category' => 'Biographie',
                'language' => 'Francais',
                'summary' => 'Une biographie documentee sur le parcours, la vision et la personnalite d un entrepreneur majeur de la tech.',
                'coverTheme' => 'blue',
                'publishedYear' => 2011,
                'pageCount' => 688,
                'availableCopies' => 2,
                'featured' => false,
                'slug' => 'steve-jobs-biographie',
            ],
            [
                'title' => 'Fondation',
                'author' => 'Isaac Asimov',
                'category' => 'Science-Fiction',
                'language' => 'Francais',
                'summary' => 'Une reflexion sur la prediction historique et la chute des empires a travers la creation d une fondation scientifique.',
                'coverTheme' => 'teal',
                'publishedYear' => 1951,
                'pageCount' => 320,
                'availableCopies' => 5,
                'featured' => true,
                'slug' => 'fondation-asimov',
            ],
            [
                'title' => 'Don Quichotte',
                'author' => 'Miguel de Cervantes',
                'category' => 'Classique',
                'language' => 'Espagnol',
                'summary' => 'Un grand classique satirique sur l ideal chevaleresque, l imagination et la frontiere entre reve et realite.',
                'coverTheme' => 'yellow',
                'publishedYear' => 1605,
                'pageCount' => 863,
                'availableCopies' => 2,
                'featured' => false,
                'slug' => 'don-quichotte',
            ],
        ];

        foreach ($books as $index => $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setAuthor($data['author']);
            $book->setCategory($data['category']);
            $book->setLanguage($data['language']);
            $book->setSummary($data['summary']);
            $book->setCoverTheme($data['coverTheme']);
            $book->setPublishedYear($data['publishedYear']);
            $book->setPageCount($data['pageCount']);
            $book->setAvailableCopies($data['availableCopies']);
            $book->setFeatured($data['featured']);
            $book->setSlug($data['slug']);
            $book->setImageUrl('https://picsum.photos/seed/'.rawurlencode($data['slug']).'/400/600');

            $manager->persist($book);
            $this->addReference(self::BOOK_REFERENCE_PREFIX.($index + 1), $book);
        }

        $manager->flush();
    }
}
