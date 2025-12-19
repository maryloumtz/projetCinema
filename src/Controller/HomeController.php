<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\ContactClientFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('client/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    public function new(Request $request): Response
    {
        $message = new Message();
        // ...

        $form = $this->createForm(ContactClientFormType::class, $message);

        return $this->render('client/form-contact/form.html.twig', [
            'form' => $form,
        ]);
    }
}
