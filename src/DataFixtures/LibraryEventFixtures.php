<?php

namespace App\DataFixtures;

use App\Entity\LibraryEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LibraryEventFixtures extends Fixture
{
    public const EVENT_REFERENCE_PREFIX = 'event_';

    public function load(ObjectManager $manager): void
    {
        $baseDate = new \DateTimeImmutable('tomorrow 18:00');

        $events = [
            [
                'title' => 'Rencontre auteur: ecrire le reel',
                'speaker' => 'Nora Bensaid',
                'description' => 'Une discussion sur les coulisses de l ecriture documentaire et le travail de terrain.',
                'location' => 'Auditorium principal',
                'capacity' => 80,
                'theme' => 'Litterature',
                'startsAt' => $baseDate,
            ],
            [
                'title' => 'Atelier jeunesse: inventer un conte',
                'speaker' => 'Claire Muller',
                'description' => 'Un atelier pratique pour imaginer des personnages et construire un recit en groupe.',
                'location' => 'Salle jeunesse',
                'capacity' => 24,
                'theme' => 'Jeunesse',
                'startsAt' => $baseDate->modify('+3 days'),
            ],
            [
                'title' => 'Cycle SF: mondes et societes',
                'speaker' => 'Lucas Ferret',
                'description' => 'Conference sur la science-fiction comme laboratoire d idees politiques et sociales.',
                'location' => 'Salle polyvalente',
                'capacity' => 60,
                'theme' => 'Science-Fiction',
                'startsAt' => $baseDate->modify('+7 days'),
            ],
            [
                'title' => 'Club lecture du mois',
                'speaker' => 'Equipe BiblioConnect',
                'description' => 'Echange convivial autour d une selection de romans et recommandations des lecteurs.',
                'location' => 'Espace lecture',
                'capacity' => 30,
                'theme' => 'Club Lecture',
                'startsAt' => $baseDate->modify('+12 days'),
            ],
            [
                'title' => 'Decouverte polar et enquete',
                'speaker' => 'Henri Valette',
                'description' => 'Panorama des codes du roman policier et des nouvelles tendances du thriller.',
                'location' => 'Auditorium principal',
                'capacity' => 70,
                'theme' => 'Policier',
                'startsAt' => $baseDate->modify('+18 days'),
            ],
        ];

        foreach ($events as $index => $data) {
            $event = new LibraryEvent();
            $event->setTitle($data['title']);
            $event->setSpeaker($data['speaker']);
            $event->setDescription($data['description']);
            $event->setLocation($data['location']);
            $event->setCapacity($data['capacity']);
            $event->setTheme($data['theme']);
            $event->setStartsAt($data['startsAt']);

            $manager->persist($event);
            $this->addReference(self::EVENT_REFERENCE_PREFIX.($index + 1), $event);
        }

        $manager->flush();
    }
}
