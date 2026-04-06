<?php

namespace App\DataFixtures;

use App\Entity\EventRegistration;
use App\Entity\LibraryEvent;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventRegistrationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $registrations = [
            [UserFixtures::USER_REFERENCE_PREFIX.'1', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'1'],
            [UserFixtures::USER_REFERENCE_PREFIX.'2', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'1'],
            [UserFixtures::USER_REFERENCE_PREFIX.'3', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'2'],
            [UserFixtures::USER_REFERENCE_PREFIX.'4', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'3'],
            [UserFixtures::USER_REFERENCE_PREFIX.'5', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'3'],
            [UserFixtures::USER_REFERENCE_PREFIX.'6', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'4'],
            [UserFixtures::USER_REFERENCE_PREFIX.'7', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'5'],
            [UserFixtures::USER_REFERENCE_PREFIX.'8', LibraryEventFixtures::EVENT_REFERENCE_PREFIX.'2'],
        ];

        foreach ($registrations as [$userRef, $eventRef]) {
            /** @var User $user */
            $user = $this->getReference($userRef, User::class);
            /** @var LibraryEvent $event */
            $event = $this->getReference($eventRef, LibraryEvent::class);

            $registration = (new EventRegistration())
                ->setUser($user)
                ->setEvent($event);

            $manager->persist($registration);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            LibraryEventFixtures::class,
        ];
    }
}
