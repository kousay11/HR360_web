<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\TwilioService;
use App\Service\GrammarCheckerService;

class TestiiiController extends AbstractController
{
    #[Route('/test-grammar', name: 'test_grammar')]
    public function testGrammar(GrammarCheckerService $grammarChecker): Response
    {
        $testText = "Je suis une phrase avec une erreur gramatique.";
        $result = $grammarChecker->checkGrammar($testText);
        
        dd($result); // Affiche le résultat brut
    }
    #[Route('/test-sms')]
    public function testSms(TwilioService $twilio): Response
    {
        try {
            // Numéro doit être vérifié dans la console Twilio pour les comptes d'essai
            $testNumber = '+21625350650'; 
            $messageSid = $twilio->getClient()->messages->create($testNumber, [
                'from' => $_ENV['twilio_from_number'],
                'body' => 'Test de fonctionnement Twilio'
            ]);
            
            
    
            return $this->json([
                'status' => 'success',
                'message_sid' => $messageSid->sid,
                'message' => 'SMS envoyé avec succès'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    #[Route('/verify-tunisian-number')]
public function verifyNumber(TwilioService $twilio): JsonResponse
{
    // Créer un service de vérification (une seule fois est suffisant en général)
    $verificationService = $twilio->getClient()->verify->v2->services
        ->create("HR360 Verification");

    // Dump du service pour voir le SID et autres détails
    dd($verificationService);

    // Lancer la vérification SMS (utiliser $verificationService->sid)
    $twilio->getClient()->verify->v2->services($verificationService->sid)
        ->verifications
        ->create('+21625350650', 'sms');

    return $this->json([
        'status' => 'verification_initiated',
        'sid' => $verificationService->sid
    ]);
}

}