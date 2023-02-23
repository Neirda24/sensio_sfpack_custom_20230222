<?php

declare(strict_types=1);

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotNull;

final class Contact
{
    #[NotNull]
    #[Length(min: 2)]
    #[NotEqualTo(value: 'test')]
    public string $firstname;

    #[NotNull]
    #[Length(min: 2, minMessage: 'Cannot be under {{ limit }}.')]
    #[Choice(
        choices: ['plop', 'test'],
        match: false
    )]
    public string $lastname;

    #[NotNull]
    #[Email()]
    public string $email;

    #[NotNull]
    public string $phone;

    #[NotNull]
    #[Length(min: 20)]
    public string $comment;

    public function __construct(
        string $firstname,
        string $lastname,
        string $email,
        string $phone,
        string $comment,
    ) {
        $this->comment   = $comment;
        $this->phone     = $phone;
        $this->email     = $email;
        $this->lastname  = $lastname;
        $this->firstname = $firstname;
    }

    public static function empty(): self
    {
        return new self(
            '',
            '',
            '',
            '',
            '',
        );
    }
}
