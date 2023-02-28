<?php

namespace App\Command;

use App\Entity\Movie as MovieEntity;
use App\Omdb\OmdbApiClient;
use App\ReadModel\Rated;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;
use Throwable;
use function array_key_first;
use function array_reduce;
use function count;
use function sprintf;

#[AsCommand(
    name: 'app:omdb:movies:import',
    description: 'Import one or more movies from OMDB',
)]
class OmdbMoviesImportCommand extends Command
{
    public function __construct(
        private readonly OmdbApiClient          $omdbApiClient,
        private readonly SluggerInterface       $slugger,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id-or-title', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'IMDB ID to import or title to search')
            ->addOption('dry-run', mode: InputOption::VALUE_NONE, description: 'Will not import in database.')
            ->setHelp(<<<EOT
            The %command.name% provides a way to import either an IMDB ID or search a title.
            
            Searching a title will asks you to choose amongst multiple possibilities.
                <info>%command.full_name% "avatar" "le sens de la fete"</info>
            EOT,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io          = new SymfonyStyle($input, $output);
        $idsOrTitles = $input->getArgument('id-or-title');

        $io->title('OMDB Import');
        $io->note(sprintf('Trying to import %d movies.', count($idsOrTitles)));

        $moviesImported = [];
        $moviesFailure  = [];

        foreach ($idsOrTitles as $idOrTitle) {
            $movie = $this->import($io, $idOrTitle);

            if (null === $movie) {
                $moviesFailure[] = $idOrTitle;
            } else {
                $moviesImported[] = [$idOrTitle, $movie];
            }
        }

        if ($input->getOption('dry-run') === false) {
            $this->entityManager->flush();
        }

        if ([] !== $moviesImported) {
            $verb = $input->getOption('dry-run') === false ? 'were' : 'would be';
            $io->success("Those movies {$verb} imported:");
            $io->table(
                ['ID', 'Search query', 'Title', 'Year'],
                array_reduce($moviesImported, static function (array $row, array $movieImported): array {
                    /** @var MovieEntity $movie */
                    [$idOrTitle, $movie] = $movieImported;

                    $row[] = [$movie->getId(), $idOrTitle, $movie->getTitle(), $movie->getReleasedAt()->format('Y')];

                    return $row;
                }, []),
            );
        }

        if ([] !== $moviesFailure) {
            $io->error('Those search terms could not be found:');
            $io->listing(
                $moviesFailure,
            );
        }

        return Command::SUCCESS;
    }

    private function import(SymfonyStyle $io, string $idOrTitle): ?MovieEntity
    {
        $io->section("'{$idOrTitle}'");

        return $this->tryImportAsImdbId($io, $idOrTitle) ?? $this->searchAndImportByTitle($io, $idOrTitle);
    }

    private function tryImportAsImdbId(SymfonyStyle $io, string $imdbId): ?MovieEntity
    {
        try {
            $result = $this->omdbApiClient->getById($imdbId);
        } catch (Throwable) {
            return null;
        }

        $newMovie = (new MovieEntity())
            ->setTitle($result['Title'])
            ->setRated(Rated::tryFrom($result['Rated']) ?? Rated::GeneralAudiences)
            ->setPoster($result['Poster'])
            ->setReleasedAt(new DateTimeImmutable($result['Released']))
            ->setSlug($this->slugger->slug("{$result['Title']}-{$result['Year']}"));

        $this->entityManager->persist($newMovie);

        return $newMovie;
    }

    private function searchAndImportByTitle(SymfonyStyle $io, string $title): ?MovieEntity
    {
        try {
            $searchResults = $this->omdbApiClient->searchByName($title);
        } catch (Throwable) {
            return null;
        }

        if (count($searchResults) === 0) {
            return null;
        }

        /** @var array<string, string> $choices */
        $choices = array_reduce($searchResults, static function (array $choices, array $searchResult): array {
            $choices[$searchResult['imdbID']] = "{$searchResult['Title']} ({$searchResult['Year']})";

            return $choices;
        }, []);

        if (count($choices) === 1) {
            $selectedChoice = array_key_first($choices);
            $io->info("'$selectedChoice' found.");
        } else {
            $selectedChoice = $io->choice('Which movie would you like to import ?', $choices);
        }

        return $this->tryImportAsImdbId($io, $selectedChoice);
    }
}
