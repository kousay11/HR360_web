<?php

namespace App\Controller;
use App\Entity\Entretien;
use App\Form\EntretienType;
use App\Entity\Evaluation;
use App\Form\EvaluationType;
use App\Repository\EvaluationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evaluation')]
final class EvaluationController extends AbstractController
{
    #[Route(name: 'app_evaluation_index', methods: ['GET'])]
    public function index(EvaluationRepository $evaluationRepository): Response
    {
        return $this->render('evaluation/index.html.twig', [
            'evaluations' => $evaluationRepository->findAll(),
        ]);
    }

    #[Route('/evafront',name: 'app_evaluation_front', methods: ['GET'])]
    public function indexfront(EvaluationRepository $evaluationRepository): Response
    {
        return $this->render('evaluation/evafront.html.twig', [
            'evaluations' => $evaluationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_evaluation_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $evaluation = new Evaluation();
    $form = $this->createForm(EvaluationType::class, $evaluation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Vérifier si l'entretien a déjà une évaluation
        if ($evaluation->getEntretien()) {
            $existingEvaluation = $entityManager->getRepository(Evaluation::class)
                ->findOneBy(['entretien' => $evaluation->getEntretien()]);
            
            if ($existingEvaluation) {
                $this->addFlash('warning', 'Cet entretien a déjà une évaluation associée.');
                return $this->redirectToRoute('app_evaluation_new');
            }
        }

        $entityManager->persist($evaluation);
        $entityManager->flush();

        if ($evaluation->getEntretien()) {
            return $this->redirectToRoute('app_evaluation_index_for_entretien', [
                'idEntretien' => $evaluation->getEntretien()->getIdEntretien()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('evaluation/new.html.twig', [
        'evaluation' => $evaluation,
        'form' => $form,
    ]);
}

    #[Route('/{idEvaluation}', name: 'app_evaluation_show', methods: ['GET'])]
    public function show(Evaluation $evaluation): Response
    {
        return $this->render('evaluation/show.html.twig', [
            'evaluation' => $evaluation,
        ]);
    }

    /*#[Route('/{idEvaluation}/edit', name: 'app_evaluation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvaluationType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evaluation/edit.html.twig', [
            'evaluation' => $evaluation,
            'form' => $form,
        ]);
    }*/

    #[Route('/{idEvaluation}/edit', name: 'app_evaluation_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(EvaluationType::class, $evaluation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        // Check if we should redirect back to entretien's evaluations
        if ($request->request->has('redirect_to_entretien') && $evaluation->getEntretien()) {
            return $this->redirectToRoute('app_evaluation_index_for_entretien', [
                'idEntretien' => $evaluation->getEntretien()->getIdEntretien()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('evaluation/edit.html.twig', [
        'evaluation' => $evaluation,
        'form' => $form,
    ]);
}

    /*#[Route('/{idEvaluation}', name: 'app_evaluation_delete', methods: ['POST'])]
    public function delete(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evaluation->getIdEvaluation(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evaluation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evaluation_index_for_entretien', [], Response::HTTP_SEE_OTHER);
    }*/


    #[Route('/{idEvaluation}', name: 'app_evaluation_delete', methods: ['POST'])]
public function delete(Request $request, Evaluation $evaluation, EntityManagerInterface $entityManager): Response
{
    if ($this->isCsrfTokenValid('delete'.$evaluation->getIdEvaluation(), $request->getPayload()->getString('_token'))) {
        // Stocker l'ID de l'entretien avant suppression
        $entretienId = $evaluation->getEntretien() ? $evaluation->getEntretien()->getIdEntretien() : null;
        
        $entityManager->remove($evaluation);
        $entityManager->flush();

        // Rediriger vers la liste des évaluations de l'entretien si applicable
        if ($entretienId) {
            return $this->redirectToRoute('app_evaluation_index_for_entretien', [
                'idEntretien' => $entretienId
            ], Response::HTTP_SEE_OTHER);
        }
    }

    return $this->redirectToRoute('app_evaluation_index', [], Response::HTTP_SEE_OTHER);
}



#[Route('/entretien/{idEntretien}', name: 'app_evaluation_index_for_entretien', methods: ['GET'])]
public function indexForEntretien(Entretien $entretien, EvaluationRepository $evaluationRepository): Response
{
    $evaluations = $evaluationRepository->findBy(['entretien' => $entretien], ['dateEvaluation' => 'DESC']);
    $Candidature=$entretien->getCandidature();
    return $this->render('evaluation/index.html.twig', [
        'evaluations' => $evaluations,
        'entretien' => $entretien,
        'candidature' => $Candidature
    ]);
}


#[Route('/new/entretien/{idEntretien}', name: 'app_evaluation_new_for_entretien', methods: ['GET', 'POST'])]
public function newForEntretien(Request $request, Entretien $entretien, EntityManagerInterface $entityManager, EvaluationRepository $evaluationRepository): Response
{
    // Vérifier si l'entretien a déjà une évaluation
    $existingEvaluation = $evaluationRepository->findOneBy(['entretien' => $entretien]);
    
    if ($existingEvaluation) {
        $this->addFlash('warning', 'Cet entretien a déjà une évaluation associée.');
        return $this->redirectToRoute('app_evaluation_index_for_entretien', [
            'idEntretien' => $entretien->getIdEntretien()
        ]);
    }

    $evaluation = new Evaluation();
    $evaluation->setEntretien($entretien);
    
    $form = $this->createForm(EvaluationType::class, $evaluation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($evaluation);
        $entityManager->flush();

        $this->addFlash('success', 'Évaluation créée avec succès.');
        return $this->redirectToRoute('app_evaluation_index_for_entretien', [
            'idEntretien' => $entretien->getIdEntretien()
        ], Response::HTTP_SEE_OTHER);
    }

    return $this->render('evaluation/new.html.twig', [
        'evaluation' => $evaluation,
        'form' => $form,
        'entretien' => $entretien,
    ]);
}

#[Route('/enteva/{idEntretien}', name: 'app_evaluation_front_for_entretien', methods: ['GET'])]
public function indexForEntretienfront(Entretien $entretien, EvaluationRepository $evaluationRepository): Response
{
    $evaluations = $evaluationRepository->findBy(['entretien' => $entretien], ['dateEvaluation' => 'DESC']);
    
    return $this->render('evaluation/evafront.html.twig', [
        'evaluations' => $evaluations,
        'entretien' => $entretien,
    ]);
}


}
