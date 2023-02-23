<?php

declare(strict_types=1);

namespace App\Controller;

use App\ReadModel\Movie;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    #[Route(
        '/api/movies/{slug}',
        name: 'movies_details',
        requirements: [
            'slug' => '[a-zA-Z0-9-]+',
        ],
        methods: ['GET']
    )]
    public function __invoke(MovieRepository $movieRepository, string $slug): Response
    {
        return $this->json($movieRepository->fetchOneBySlug($slug));
    }
}
