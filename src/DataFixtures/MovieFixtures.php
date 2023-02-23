<?php

namespace App\DataFixtures;

use App\Entity\Genre;
use App\Entity\Movie;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MovieFixtures extends Fixture implements DependentFixtureInterface
{
    private const MOVIES = [
        [
            'title'      => 'Avatar',
            'slug'       => 'avatar',
            'poster'     => 'https://m.media-amazon.com/images/M/MV5BZDA0OGQxNTItMDZkMC00N2UyLTg3MzMtYTJmNjg3Nzk5MzRiXkEyXkFqcGdeQXVyMjUzOTY1NTc@._V1_SX300.jpg',
            'releasedAt' => '2009-12-16',
            'genres'     => [
                'Action',
                'Adventure',
                'Fantasy',
            ],
        ],
        [
            'title'      => 'Les Figures de l\'ombre',
            'slug'       => 'les-figures-de-l-ombre',
            'poster'     => 'https://m.media-amazon.com/images/M/MV5BMzg2Mzg4YmUtNDdkNy00NWY1LWE3NmEtZWMwNGNlMzE5YzU3XkEyXkFqcGdeQXVyMjA5MTIzMjQ@._V1_SX300.jpg',
            'releasedAt' => '2017-03-08',
            'genres'     => [
                'Biography',
                'Drama',
                'History',
            ],
        ],
        [
            'title'      => 'Le Dîner de Cons',
            'slug'       => 'le-diner-de-cons',
            'poster'     => 'https://m.media-amazon.com/images/M/MV5BZjFkOWM5NDUtODYwOS00ZDg0LWFkZGUtYzBkYzNjZjU3ODE3XkEyXkFqcGdeQXVyNzQzNzQxNzI@._V1_SX300.jpg',
            'releasedAt' => '1998-04-15',
            'genres'     => [
                'Comedy',
            ],
        ],
        [
            'title'      => 'Une Merveilleuse Histoire du Temps',
            'slug'       => 'une-merveilleuse-histoire-du-temps',
            'poster'     => 'https://m.media-amazon.com/images/M/MV5BMTAwMTU4MDA3NDNeQTJeQWpwZ15BbWU4MDk4NTMxNTIx._V1_SX300.jpg',
            'releasedAt' => '2014-11-26',
            'genres'     => [
                'Biography',
                'Drama',
                'Romance',
            ],
        ],
        [
            'title'      => 'Eva',
            'slug'       => 'eva',
            'poster'     => 'https://m.media-amazon.com/images/M/MV5BODUwNjQyODAzMF5BMl5BanBnXkFtZTcwMTUxOTIwNw@@._V1_SX300.jpg',
            'releasedAt' => '2015-03-13',
            'genres'     => [
                'Adventure',
                'Drama',
                'Fantasy',
            ],
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::MOVIES as $movie) {
            $newMovie = (new Movie())
                ->setTitle($movie['title'])
                ->setPoster($movie['poster'])
                ->setSlug($movie['slug'])
                ->setReleasedAt(DateTimeImmutable::createFromFormat('Y-m-d', $movie['releasedAt']));

            foreach ($movie['genres'] as $genreName) {
                $newMovie->addGenre($this->getGenre($genreName));
            }

            $manager->persist($newMovie);
        }

        $manager->flush();
    }

    private function getGenre(string $name): Genre
    {
        return $this->getReference("genre.{$name}");
    }

    public function getDependencies(): array
    {
        return [
            GenreFixtures::class,
        ];
    }
}
