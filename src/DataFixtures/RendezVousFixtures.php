<?php

namespace App\DataFixtures;

use App\Entity\Prestation;
use App\Entity\RendezVous;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RendezVousFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $samples = [
            // [client_ref_index, prestation_index, date, statut]
            [0, 0,  '+2 days 09:00',  RendezVous::STATUT_EN_ATTENTE],
            [1, 2,  '+3 days 10:30',  RendezVous::STATUT_CONFIRME],
            [2, 7,  '+1 day 14:00',   RendezVous::STATUT_EN_ATTENTE],
            [3, 4,  '+5 days 11:00',  RendezVous::STATUT_CONFIRME],
            [4, 9,  '+7 days 15:30',  RendezVous::STATUT_EN_ATTENTE],
            [0, 6,  '-3 days 10:00',  RendezVous::STATUT_TERMINE],
            [1, 1,  '-7 days 14:00',  RendezVous::STATUT_TERMINE],
            [2, 11, '-1 day 09:30',   RendezVous::STATUT_ANNULE],
        ];

        foreach ($samples as [$ci, $pi, $dateStr, $statut]) {
            $rdv = new RendezVous();
            $rdv->setClient($this->getReference(UserFixtures::CLIENT_REF.$ci, User::class))
                ->setPrestation($this->getReference(PrestationFixtures::PREFIX.$pi, Prestation::class))
                ->setDateHeure(new \DateTime($dateStr))
                ->setStatut($statut);
            $manager->persist($rdv);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class, PrestationFixtures::class];
    }
}
