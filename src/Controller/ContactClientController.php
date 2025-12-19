<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactClientController extends AbstractController
{
    #[Route('/contact/client', name: 'app_contact_client')]
    public function index(): Response
    {
        return $this->render('contact_client/index.html.twig', [
            'controller_name' => 'ContactClientController',
        ]);
    }
}
