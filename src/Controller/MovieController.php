<?php

declare(strict_types=1);

namespace App\Controller;

use App\ApiModel\CreateMovie;
use App\Entity\Movie as MovieEntity;
use App\Omdb\OmdbApiClient;
use App\ReadModel\Movie;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;
use function count;

#[Route('/api/movies')]
class MovieController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface  $validator,
        private readonly SluggerInterface    $slugger,
        private readonly GenreRepository     $genreRepository,
        private readonly MovieRepository     $movieRepository,
        private readonly OmdbApiClient       $omdbApiClient,
    ) {
    }

    #[Route(
        '/{slug}',
        name: 'api_movies_details',
        requirements: [
            'slug' => '[a-zA-Z0-9-]+',
        ],
        methods: ['GET']
    )]
    public function get(string $slug): Response
    {
        try {
            $movie = Movie::fromMovieEntity($this->movieRepository->fetchOneBySlug($slug));
        } catch (NoResultException) {
            try {
                $movie = Movie::fromMovieApi($this->omdbApiClient->getById($slug), $this->slugger);
            } catch (Throwable $throwable) {
                throw $this->createNotFoundException("Could not find the movie slug or IMDB ID : '{$slug}'", $throwable);
            }
        }

        return $this->json($movie);
    }

    #[Route(
        '',
        name: 'api_movies_create',
        methods: ['POST']
    )]
    public function create(
        Request $request,
    ): Response {
        /** @var CreateMovie $createMovie */
        $createMovie = $this->serializer->deserialize(
            $request->getContent(),
            CreateMovie::class,
            $request->getContentTypeFormat(),
        );
        $violations  = $this->validator->validate($createMovie);

        if (count($violations) > 0) {
            return $this->json($violations);
        }

        $movieEntity = (new MovieEntity())
            ->setTitle($createMovie->title)
            ->setReleasedAt($createMovie->releasedAt)
            ->setPoster($createMovie->poster)
            ->setSlug($this->slugger->slug("{$createMovie->title}-{$createMovie->getReleasedYear()}")->toString());
        foreach ($createMovie->genres as $genreName) {
            $movieEntity->addGenre($this->genreRepository->getOrCreate($genreName));
        }
        $this->movieRepository->save($movieEntity, true);

        return new JsonResponse(
            status: Response::HTTP_CREATED,
            headers: [
                'Location' => $this->generateUrl('api_movies_details', ['slug' => $movieEntity->getSlug()]),
            ]
        );
    }
}
