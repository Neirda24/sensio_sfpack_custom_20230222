<?php

declare(strict_types=1);

namespace App\ReadModel;

use App\Entity\Genre as GenreEntity;
use App\Entity\Movie as MovieEntity;
use App\Omdb\OmdbApiClient;
use DateTimeImmutable;
use Symfony\Component\String\Slugger\SluggerInterface;
use function array_map;
use function explode;

/**
 * @phpstan-import-type GetResponseById from OmdbApiClient
 */
final class Movie
{
    public readonly string $releasedAt;

    /**
     * @param list<string> $genres
     */
    public function __construct(
        public readonly int|string $id,
        public readonly string     $title,
        public readonly string     $slug,
        public readonly string     $poster,
        DateTimeImmutable          $releasedAt,
        public readonly array      $genres,
    ) {
        $this->releasedAt = $releasedAt->format('c');
    }

    public static function fromMovieEntity(MovieEntity $movieEntity): self
    {
        return new Movie(
            id: $movieEntity->getId(),
            title: $movieEntity->getTitle(),
            slug: $movieEntity->getSlug(),
            poster: $movieEntity->getPoster(),
            releasedAt: $movieEntity->getReleasedAt(),
            genres: array_map(static function (GenreEntity $genreEntity): string {
                return $genreEntity->getName();
            }, $movieEntity->getGenres()->toArray())
        );
    }

    /**
     * @param GetResponseById $movieApi
     */
    public static function fromMovieApi(array $movieApi, SluggerInterface $slugger): self
    {
        return new Movie(
            id: $movieApi['imdbID'],
            title: $movieApi['Title'],
            slug: $slugger->slug("{$movieApi['Title']}-{$movieApi['Year']}")->toString(),
            poster: $movieApi['Poster'],
            releasedAt: new DateTimeImmutable($movieApi['Released']),
            genres: explode(', ', $movieApi['Genre'])
        );
    }
}
