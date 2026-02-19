<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\ContactClientFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeClientController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('client/home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = new Message();
        $success = false;
        $errors = [];

        // Si l'utilisateur est connecté, remplir automatiquement ID_user
        if ($this->getUser()) {
            $message->setIDUser($this->getUser());
        }

        $form = $this->createForm(ContactClientFormType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                // Récupérer les erreurs du formulaire
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
            } else {
                // Si l'utilisateur est connecté, le lier au message
                // Sinon, le message sera anonyme (ID_user = null)
                if ($this->getUser()) {
                    $message->setIDUser($this->getUser());
                }

                try {
                    $entityManager->persist($message);
                    $entityManager->flush();

                    $success = true;
                    $this->addFlash('success', 'Votre message a été envoyé avec succès.');
                    return $this->redirectToRoute('app_home');
                } catch (\Exception $e) {
                    $errors[] = 'Erreur lors de l\'envoi: ' . $e->getMessage();
                }
            }
        }

        return $this->render('client/form-contact/form.html.twig', [
            'form' => $form,
            'success' => $success,
            'errors' => $errors,
        ]);
    }
}
