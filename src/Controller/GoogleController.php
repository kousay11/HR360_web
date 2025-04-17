<?php

namespace App\Controller;

use App\Service\GoogleOAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    #[Route('/google/login', name: 'google_login')]
    public function login(GoogleOAuthService $googleOAuthService): RedirectResponse
    {
        $client = $googleOAuthService->getClient();
        $authUrl = $client->createAuthUrl();

        return new RedirectResponse($authUrl);
    }

    #[Route('/google/oauth2callback', name: 'google_callback')]
    public function callback(Request $request, GoogleOAuthService $googleOAuthService): RedirectResponse
    {
        $code = $request->query->get('code');

        if (!$code) {
            throw $this->createNotFoundException('Code de validation manquant.');
        }

        $googleOAuthService->exchangeCode($code);

        return $this->redirectToRoute('app_entretien_index'); // Redirige vers la page d'accueil par exemple
    }
}
