<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactType;
use App\Form\Model\Contact;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

#[AsController]
class ContactController
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment          $twig,
    ) {
    }

    #[Route(
        '/contact',
        name: 'app_contact',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request): Response
    {
        $contact = Contact::empty();

        $contactForm = $this->formFactory->create(ContactType::class, $contact);
        $contactForm->handleRequest($request);

        $statusCode = Response::HTTP_OK;
        if ($contactForm->isSubmitted()) {
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
            if ($contactForm->isValid()) {
                dd($contact);
            }
        }

        return new Response($this->twig->render('contact.html.twig', [
            'contact_form' => $contactForm->createView(),
        ]), $statusCode);
    }
}
