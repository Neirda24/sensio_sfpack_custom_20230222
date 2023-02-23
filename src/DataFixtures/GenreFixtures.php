<?php

namespace App\DataFixtures;

use App\Entity\Genre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GenreFixtures extends Fixture
{
    private const GENRE = [
        'Action',
        'Adventure',
        'Biography',
        'Comedy',
        'Drama',
        'Fantasy',
        'History',
        'Romance',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::GENRE as $genreName) {
            $newGenre = (new Genre())->setName($genreName);

            $manager->persist($newGenre);
            $this->addReference("genre.{$genreName}", $newGenre);
        }

        $manager->flush();
    }
}
