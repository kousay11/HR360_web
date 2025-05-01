<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    #[Route('/quiz/submit-evaluation', name: 'quiz_submit_evaluation', methods: ['POST'])]
public function submitEvaluationQuiz(
    Request $request,
    EvaluationRepository $evaluationRepo,
    EntityManagerInterface $em
): Response {
    try {
        $data = json_decode($request->getContent(), true) ?: $request->request->all();
        
        if (!$data) throw new \Exception('Aucune donnée reçue');
        if (!isset($data['evaluation_id'])) throw new \Exception('ID d\'évaluation manquant');
        if (!isset($data['answers'])) throw new \Exception('Réponses manquantes');

        $evaluation = $evaluationRepo->find($data['evaluation_id']);
        if (!$evaluation) throw new \Exception('Évaluation non trouvée');

        $correctCount = 0;
        $questions = $evaluation->getQuestions();

        foreach ($questions as $index => $question) {
            $userAnswer = $data['answers'][$index] ?? null;
            $correctAnswers = $question['correctAnswers'] ?? [];
            
            if ($userAnswer && isset($correctAnswers[$userAnswer.'_correct'])) {
                if (filter_var($correctAnswers[$userAnswer.'_correct'], FILTER_VALIDATE_BOOLEAN)) {
                    $correctCount++;
                }
            }
        }

        $score = count($questions) > 0 ? round(($correctCount / count($questions)) * 10) : 0;
        $evaluation->setScorequiz($score);
        $em->flush();
 // Rediriger vers la liste des évaluations de l'entretien
 if ($evaluation->getEntretien()) {
    return $this->redirectToRoute('app_evaluation_front_for_entretien', [
        'idEntretien' => $evaluation->getEntretien()->getIdEntretien()
    ]);
}

        // Vérifiez que l'entretien existe avant de générer l'URL
        $entretien = $evaluation->getEntretien();
        if (!$entretien) {
            throw new \Exception('Aucun entretien associé à cette évaluation');
        }

        return new JsonResponse([
            'success' => true,
            'score' => $score,
            'correctCount' => $correctCount,
            'totalQuestions' => count($questions),
            'redirectUrl' => $this->generateUrl('app_evaluation_front_for_entretien', [
                'idEntretien' => $entretien->getIdEntretien()
            ])
        ]);

    } catch (\Exception $e) {
        return new JsonResponse([
            'success' => false,
            'message' => $e->getMessage(),
            'redirectUrl' => $this->generateUrl('app_evaluation_front') // Fallback URL
        ], Response::HTTP_BAD_REQUEST);
    }
}

    

    #[Route('/evaluation/{idEvaluation}/submit-quiz', name: 'app_evaluation_submit_quiz', methods: ['POST'])]
public function submitQuiz(
    Request $request,
    Evaluation $evaluation,
    EntityManagerInterface $entityManager
): Response {
    try {
        $data = $request->request->all();
        
        $correctCount = 0;
        $questions = $evaluation->getQuestions();

        foreach ($questions as $index => $question) {
            $userAnswer = $data['answers'][$index] ?? null;
            $correctAnswers = $question['correctAnswers'] ?? [];
            $correctAnswerKey = null;

            // Trouver la bonne réponse
            foreach ($correctAnswers as $answerKey => $isCorrectAnswer) {
                if (filter_var($isCorrectAnswer, FILTER_VALIDATE_BOOLEAN)) {
                    $correctAnswerKey = str_replace('_correct', '', $answerKey);
                    break;
                }
            }

            // Vérifier si la réponse de l'utilisateur est correcte
            if ($userAnswer) {
                $normalizedUserAnswer = strtolower(trim($userAnswer));
                $normalizedCorrectAnswer = strtolower(trim($correctAnswerKey));
                
                if ($normalizedUserAnswer === $normalizedCorrectAnswer) {
                    $correctCount++;
                }
            }
        }

        // Calcul du score sur 10
        $score = round(($correctCount / count($questions)) * 10);
        $evaluation->setScorequiz($score);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Quiz soumis avec succès! Score: %d/10', $score));
        
        // Rediriger vers la liste des évaluations de l'entretien
        if ($evaluation->getEntretien()) {
            return $this->redirectToRoute('app_evaluation_index_for_entretien', [
                'idEntretien' => $evaluation->getEntretien()->getIdEntretien()
            ]);
        }

        return $this->redirectToRoute('app_evaluation_index');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Erreur lors de la soumission du quiz: '.$e->getMessage());
        return $this->redirectToRoute('app_evaluation_quiz', [
            'idEvaluation' => $evaluation->getIdEvaluation()
        ]);
    }
}

}