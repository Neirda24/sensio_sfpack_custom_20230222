<?php

declare(strict_types=1);

namespace App\Omdb;

use LogicException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * @phpstan-type GetByIdResponse array{Title: string, Year: string, Rated: string, Released: string, Genre: string, Poster: string, imdbID: string}
 */
final class OmdbApiClient
{
    public function __construct(private readonly HttpClientInterface $omdbApiClient)
    {
    }

    /**
     * @return GetByIdResponse
     */
    public function getById(string $omdbId): array
    {
        $response = $this->omdbApiClient->request('GET', '/', [
            'query' => [
                'i'    => $omdbId,
                'type' => 'movie',
                'plot' => 'full',
                'r'    => 'json',
            ],
        ]);

        try {
            $response = $response->toArray();

            if ('False' === $response['Response']) {
                throw new LogicException('Not found');
            }

            return $response;
        } catch (Throwable) {
            throw new LogicException('Not found');
        }
    }
}
