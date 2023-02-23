<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactType;
use App\Form\Model\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route(
        '/contact',
        name: 'app_contact',
        methods: ['GET', 'POST']
    )]
    public function __invoke(Request $request): Response
    {
        $contact = Contact::empty();

        $contactForm = $this->createForm(ContactType::class, $contact);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            dump($contact);
        }

        return $this->render('contact.html.twig', [
            'contact_form' => $contactForm->createView(),
        ]);
    }
}
