<?php

declare(strict_types=1);

namespace App\Omdb;

use LogicException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final class OmdbApiClient
{
    public function __construct(private readonly HttpClientInterface $omdbApiClient)
    {
    }

    /**
     * @return array{Title: string, Year: string, Rated: string, Released: string, Genre: string, Poster: string, imdbID: string}
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

    /**
     * @return list<array{Title: string, Year: string, imdbID: string, Type: string, Poster: string}>
     */
    public function searchByName(string $name): array
    {
        $response = $this->omdbApiClient->request('GET', '/', [
            'query' => [
                'type'   => 'movie',
                'r'      => 'json',
                'page'   => '1',
                's'      => $name,
            ],
        ]);

        $response = $response->toArray();

        if ('False' === $response['Response']) {
            return [];
        }

        return $response['Search'];
    }
}
