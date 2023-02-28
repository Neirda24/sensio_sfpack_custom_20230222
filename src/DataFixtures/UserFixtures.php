<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Psr\Clock\ClockInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

class UserFixtures extends Fixture
{
    /**
     * @var list<array{username: string, password: string, birthdate: string, age: int, admin: bool}>
     */
    private const USERS = [
        [
            'username'  => 'Adrien',
            'password'  => 'Adrien',
            'birthdate' => '20/02',
            'age'       => 32,
            'admin'     => true,
        ],
        [
            'username'  => 'Max',
            'password'  => 'Max',
            'birthdate' => '02/05',
            'age'       => 16,
            'admin'     => false,
        ],
        [
            'username'  => 'Louise',
            'password'  => 'Louise',
            'birthdate' => '07/12',
            'age'       => 3,
            'admin'     => false,
        ],
    ];

    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
        private readonly ClockInterface                 $clock,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $userData) {
            $user = (new User())
                ->setUsername($userData['username'])
                ->setPassword($this->passwordHasherFactory->getPasswordHasher(User::class)->hash($userData['password']))
                ->setBirthdate(DateTimeImmutable::createFromFormat(
                    '!d/m/Y',
                    $userData['birthdate'] . '/' . $this->clock->now()->modify("-{$userData['age']} years")->format('Y'),
                ));

            if (true === $userData['admin']) {
                $user->setRoles(['ROLE_ADMIN']);
            }

            $manager->persist($user);
        }

        $manager->flush();
    }
}
