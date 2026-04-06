<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const ADMIN_REFERENCE = 'user_admin';
    public const LIBRARIAN_REFERENCE = 'user_librarian';
    public const USER_REFERENCE_PREFIX = 'user_standard_';

    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = $this->createUser(
            email: 'dianengameni8@gmail.com',
            nom: 'Ngameni',
            prenom: 'Diane',
            roles: ['ROLE_ADMIN'],
            plainPassword: 'Admin1234!'
        );
        $manager->persist($admin);
        $this->addReference(self::ADMIN_REFERENCE, $admin);

        $librarian = $this->createUser(
            email: 'biblio.manager@biblioconnect.fr',
            nom: 'Muller',
            prenom: 'Claire',
            roles: ['ROLE_LIBRARIAN'],
            plainPassword: 'Library123!'
        );
        $manager->persist($librarian);
        $this->addReference(self::LIBRARIAN_REFERENCE, $librarian);

        $standardUsers = [
            ['email' => 'alice.martin@biblioconnect.fr', 'nom' => 'Martin', 'prenom' => 'Alice'],
            ['email' => 'julien.dupont@biblioconnect.fr', 'nom' => 'Dupont', 'prenom' => 'Julien'],
            ['email' => 'sarah.diallo@biblioconnect.fr', 'nom' => 'Diallo', 'prenom' => 'Sarah'],
            ['email' => 'hugo.lemaire@biblioconnect.fr', 'nom' => 'Lemaire', 'prenom' => 'Hugo'],
            ['email' => 'ines.morel@biblioconnect.fr', 'nom' => 'Morel', 'prenom' => 'Ines'],
            ['email' => 'omar.benali@biblioconnect.fr', 'nom' => 'Benali', 'prenom' => 'Omar'],
            ['email' => 'lea.rivierre@biblioconnect.fr', 'nom' => 'Rivierre', 'prenom' => 'Lea'],
            ['email' => 'nathan.carpentier@biblioconnect.fr', 'nom' => 'Carpentier', 'prenom' => 'Nathan'],
        ];

        foreach ($standardUsers as $index => $profile) {
            $user = $this->createUser(
                email: $profile['email'],
                nom: $profile['nom'],
                prenom: $profile['prenom'],
                roles: ['ROLE_USER'],
                plainPassword: 'User1234!'
            );

            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE_PREFIX.($index + 1), $user);
        }

        $manager->flush();
    }

    private function createUser(string $email, string $nom, string $prenom, array $roles, string $plainPassword): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setRoles($roles);
        $user->setIsVerified(true);
        $user->setPassword($this->hasher->hashPassword($user, $plainPassword));

        return $user;
    }
}
