<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Karser\Recaptcha3Bundle\Services\RecaptchaValidatorInterface;
use App\Form\LoginType;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;




class AuthController extends AbstractController
{

    public function __construct(private HttpClientInterface $httpClient) {}#[Route('/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        ParameterBagInterface $params,
        Request $request
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
    
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
    
        if ($request->isMethod('POST')) {
            $recaptchaResponse = $request->request->get('g-recaptcha-response');
            $secret = $params->get('recaptcha3_secret');
    
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $secret,
                    'response' => $recaptchaResponse,
                ]
            ]);
    
            $data = $response->toArray();
    
            if (!$data['success']) {
                $error = new AuthenticationException('Veuillez valider le reCAPTCHA.');
                $this->addFlash('error', 'Veuillez valider le reCAPTCHA.');
                return $this->redirectToRoute('app_login');            
            }
        }
    
        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'recaptcha_site_key' => $params->get('recaptcha3_key'),
        ]);
    }
    


    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
