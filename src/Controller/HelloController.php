<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    #[Route(
        '/hello/{name}',
        name: 'app_hello',
        requirements: [
            'name' => '[a-zA-Z-]+',
        ],
        defaults: [
            'name' => 'Adrien',
        ],
        methods: ['GET']
    )]
    public function index(string $name): Response
    {
        return new Response("<body>Hello {$name} !</body>");
    }
}
