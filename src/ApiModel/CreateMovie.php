<?php

declare(strict_types=1);

namespace App\ApiModel;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraints\Count;

final class CreateMovie
{
    public string $title;

    public string $poster;

    public DateTimeImmutable $releasedAt;

    /**
     * @var list<string>
     */
    #[Count(min: 1)]
    public array $genres;
}
