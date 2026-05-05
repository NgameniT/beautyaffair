<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_REF  = 'user_admin';
    public const CLIENT_REF = 'user_client_';

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = $this->make('dianengameni8@gmail.com', 'Ngameni', 'Diane', ['ROLE_ADMIN'], 'Admin1234!');
        $manager->persist($admin);
        $this->addReference(self::ADMIN_REF, $admin);

        // Clients de test
        $clients = [
            ['email' => 'amina.bello@gmail.com',   'nom' => 'Bello',   'prenom' => 'Amina'],
            ['email' => 'fatou.diallo@gmail.com',   'nom' => 'Diallo',  'prenom' => 'Fatou'],
            ['email' => 'kokou.agbeko@gmail.com',   'nom' => 'Agbeko',  'prenom' => 'Kokou'],
            ['email' => 'carine.houeto@gmail.com',  'nom' => 'Houeto',  'prenom' => 'Carine'],
            ['email' => 'rodrigue.azan@gmail.com',  'nom' => 'Azan',    'prenom' => 'Rodrigue'],
        ];

        foreach ($clients as $i => $data) {
            $user = $this->make($data['email'], $data['nom'], $data['prenom'], ['ROLE_USER'], 'User1234!');
            $manager->persist($user);
            $this->addReference(self::CLIENT_REF.$i, $user);
        }

        $manager->flush();
    }

    private function make(string $email, string $nom, string $prenom, array $roles, string $pwd): User
    {
        $user = new User();
        $user->setEmail($email)
             ->setNom($nom)
             ->setPrenom($prenom)
             ->setRoles($roles)
             ->setIsVerified(true)
             ->setPassword($this->hasher->hashPassword($user, $pwd));
        return $user;
    }
}
