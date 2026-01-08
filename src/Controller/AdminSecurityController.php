<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminSecurityController extends AbstractController
{
    private const RESET_TTL_SECONDS = 900;
    private const MAIL_FROM = 'no-reply@projetcinema.local';

    #[Route('/espace-admin/login', name: 'espace_admin_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        return $this->render('espace-admin/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/espace-admin/logout', name: 'espace_admin_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method is blank because the route is handled by the firewall.');
    }

    #[Route('/espace-admin/accueil', name: 'espace_admin_accueil', methods: ['GET'])]
    public function accueil(): Response
    {
        return $this->render('espace-admin/accueil.html.twig');
    }

    #[Route('/espace-admin/mot-de-passe-oublie', name: 'espace_admin_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        CacheItemPoolInterface $cache,
        MailerInterface $mailer
    ): Response {
        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));

            if ($email === '') {
                $this->addFlash('error', 'Veuillez saisir un email.');

                return $this->redirectToRoute('espace_admin_forgot_password');
            }

            $user = $userRepository->findOneBy(['email' => $email]);
            if ($user !== null) {
                $code = (string) random_int(100000, 999999);
                $cacheKey = $this->getResetCacheKey($email);
                $cacheItem = $cache->getItem($cacheKey);
                $cacheItem->set($code);
                $cacheItem->expiresAfter(self::RESET_TTL_SECONDS);
                $cache->save($cacheItem);

                $message = (new Email())
                    ->from(self::MAIL_FROM)
                    ->to($email)
                    ->subject('Code de reinitialisation')
                    ->text(
                        "Bonjour,\n\n"
                        ."Voici votre code de reinitialisation: {$code}\n"
                        ."Ce code expire dans 15 minutes.\n\n"
                        ."Si vous n'etes pas a l'origine de cette demande, ignorez cet email.\n"
                    );
                $mailer->send($message);
            }

            $this->addFlash(
                'success',
                'Si un compte existe pour cet email, un code a ete envoye.'
            );

            return $this->redirectToRoute('espace_admin_login');
        }

        return $this->render('espace-admin/forgot_password.html.twig');
    }

    #[Route('/espace-admin/reinitialiser', name: 'espace_admin_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        Request $request,
        UserRepository $userRepository,
        CacheItemPoolInterface $cache,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));
            $code = trim((string) $request->request->get('code'));
            $password = (string) $request->request->get('password');

            if ($email === '' || $code === '' || $password === '') {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');

                return $this->redirectToRoute('espace_admin_reset_password');
            }

            $cacheKey = $this->getResetCacheKey($email);
            $cacheItem = $cache->getItem($cacheKey);
            $expectedCode = $cacheItem->isHit() ? (string) $cacheItem->get() : null;

            if ($expectedCode === null || !hash_equals($expectedCode, $code)) {
                $this->addFlash('error', 'Code invalide ou expire.');

                return $this->redirectToRoute('espace_admin_reset_password');
            }

            $user = $userRepository->findOneBy(['email' => $email]);
            if ($user === null) {
                $this->addFlash('error', 'Impossible de reinitialiser le mot de passe.');

                return $this->redirectToRoute('espace_admin_reset_password');
            }

            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $entityManager->flush();
            $cache->deleteItem($cacheKey);

            $this->addFlash('success', 'Mot de passe mis a jour. Vous pouvez vous connecter.');

            return $this->redirectToRoute('espace_admin_login');
        }

        return $this->render('espace-admin/reset_password.html.twig');
    }

    private function getResetCacheKey(string $email): string
    {
        return 'espace_admin_reset_' . hash('sha256', strtolower($email));
    }
}
