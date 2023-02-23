<?php

declare(strict_types=1);

namespace App\Form\Model;

final class Contact
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $email,
        public string $phone,
        public string $comment,
    ) {
    }
}
