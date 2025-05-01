<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Bundle\EmailVerifierBundle\Service\EmailVerifier;
use Symfony\Component\HttpFoundation\JsonResponse;




class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        EmailVerifier $emailVerifier
    ): Response {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Vérification de l'email
                $email = $form->get('email')->getData();
                $verificationResult = $emailVerifier->verify($email);
                
                if (!$verificationResult['is_valid']) {
                    $this->addFlash('error', 'Adresse email invalide ou non délivrable');
                    return $this->redirectToRoute('app_register');
                }

                // Gestion de l'image
                $imageFile = $form->get('image')->getData();
                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                    $imageFile->move(
                        $params->get('images_directory'),
                        $newFilename
                    );
                    $user->setImage($newFilename);
                }

                // Hash du mot de passe
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $user->setRole('Candidat');
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/api/verify-email', name: 'verify_email', methods: ['POST'])]
    public function verifyEmail(
        Request $request, 
        EmailVerifier $emailVerifier
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
    
        if (empty($email)) {
            return $this->json(['valid' => false, 'error' => 'Email required']);
        }
    
        try {
            $result = $emailVerifier->verify($email);
            return $this->json([
                'valid' => $result['is_valid'],
                'message' => $result['is_valid'] 
                    ? 'Email valide' 
                    : 'Email invalide ou non délivrable'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'valid' => false,
                'error' => 'Erreur de vérification'
            ]);
        }
    }
}