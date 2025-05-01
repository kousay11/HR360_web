<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GeminiService;
use Psr\Log\LoggerInterface;
use App\Repository\FormationRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ChatController extends AbstractController
{
    #[Route('/ask-gemini', name: 'send_chat_message', methods: ['POST', 'GET'])]

    public function sendChatMessage(    
        Request $request,
        GeminiService $geminiClient,
        FormationRepository $formationRepository,
        LoggerInterface $logger
    ): JsonResponse {
        try {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            $message = trim($request->request->get('message', ''));

            if (empty($message)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Le message ne peut pas être vide'
                ], 400);
            }

            $user = $this->getUser();
            if (!$user) {
                throw new AccessDeniedException('Utilisateur non authentifié');
            }

            $employeeFormations = $formationRepository->findByEmployee($user->getId());

            $context = "Vous êtes un assistant spécialisé dans les formations. Voici les formations auxquelles l'employé participe :\n";
            foreach ($employeeFormations as $formation) {
                $context .= "- {$formation->getTitre()}: {$formation->getDescription()}\n";
            }
            $context .= "\nRépondez uniquement aux questions relatives à ces formations.\n";

            $fullMessage = $context . "Question : " . $message;

            $response = $geminiClient->sendMessage($fullMessage);

            if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                $logger->error('Réponse inattendue de Gemini API', ['response' => $response]);
                throw new \RuntimeException('Format de réponse invalide de l\'API Gemini');
            }

            return new JsonResponse([
                'success' => true,
                'message' => $response['candidates'][0]['content']['parts'][0]['text']
            ]);

        } catch (AccessDeniedException $e) {
            $logger->warning('Accès refusé au chat', ['error' => $e->getMessage()]);
            return new JsonResponse([
                'success' => false,
                'error' => 'Vous devez être connecté pour utiliser le chat.'
            ], 401);
        } catch (\Throwable $e) {
            $logger->error('Erreur de traitement du chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return new JsonResponse([
                'success' => false,
                'error' => 'Une erreur est survenue. Veuillez réessayer plus tard.'
            ], 500);
        }
    }
}
