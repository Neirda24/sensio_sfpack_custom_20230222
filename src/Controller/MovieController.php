<?php

declare(strict_types=1);

namespace App\Controller;

use App\ApiModel\CreateMovie;
use App\Entity\Movie as MovieEntity;
use App\ReadModel\Movie;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function count;

#[Route('/api/movies')]
class MovieController extends AbstractController
{
    #[Route(
        '/{slug}',
        name: 'api_movies_details',
        requirements: [
            'slug' => '[a-zA-Z0-9-]+',
        ],
        methods: ['GET']
    )]
    public function get(MovieRepository $movieRepository, string $slug): Response
    {
        return $this->json(Movie::fromMovieEntity($movieRepository->fetchOneBySlug($slug)));
    }

    #[Route(
        '',
        name: 'api_movies_create',
        methods: ['POST']
    )]
    public function create(
        Request             $request,
        SerializerInterface $serializer,
        ValidatorInterface  $validator,
        GenreRepository     $genreRepository,
        MovieRepository     $movieRepository,
    ): Response {
        /** @var CreateMovie $createMovie */
        $createMovie = $serializer->deserialize(
            $request->getContent(),
            CreateMovie::class,
            $request->getContentTypeFormat(),
        );
        $violations  = $validator->validate($createMovie);

        if (count($violations) > 0) {
            return $this->json($violations);
        }

        $movieEntity = (new MovieEntity())
            ->setTitle($createMovie->title)
            ->setReleasedAt($createMovie->releasedAt)
            ->setPoster($createMovie->poster)
            ->setSlug($createMovie->title);
        foreach ($createMovie->genres as $genreName) {
            $movieEntity->addGenre($genreRepository->getOrCreate($genreName));
        }
        $movieRepository->save($movieEntity, true);

        return new JsonResponse(
            status: Response::HTTP_CREATED,
            headers: [
                'Location' => $this->generateUrl('api_movies_details', ['slug' => $movieEntity->getSlug()])
            ]
        );
    }
}
