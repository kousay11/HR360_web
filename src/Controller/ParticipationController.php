<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\Participation;
use App\Form\ParticipationType;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/participation')]
final class ParticipationController extends AbstractController
{
    // ----------------- FRONT -----------------

    #[Route('/front', name: 'app_participation_front_index', methods: ['GET'])]
    public function indexFront(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $participations = $entityManager->getRepository(Participation::class)->findBy([
            'idUser' => $user,
        ]);

        return $this->render('participation/front/index.html.twig', [
            'participations' => $participations,
        ]);
    }

    #[Route('/front/new/{id}', name: 'app_participation_front_new', methods: ['GET', 'POST'])]
    public function newFront(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour participer.");
        }

        $formation = $entityManager->getRepository(Formation::class)->find($id);
        if (!$formation) {
            throw $this->createNotFoundException("Formation introuvable.");
        }

        $existing = $entityManager->getRepository(Participation::class)->findOneBy([
            'idUser' => $user,
            'idFormation' => $formation,
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Vous participez déjà à cette formation.');
            return $this->redirectToRoute('app_formation_front_index');
        }

        $participation = new Participation();
        $participation->setIdUser($user);
        $participation->setIdFormation($formation);

        $entityManager->persist($participation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre participation a été enregistrée avec succès !');

        return $this->redirectToRoute('app_formation_front_index');
    }


    #[Route('/front/{id}/cancel', name: 'app_participation_front_cancel', methods: ['POST'])]
    public function cancelFront(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérification que l'utilisateur est bien le participant
        if ($participation->getIdUser() !== $user) {
            throw $this->createAccessDeniedException("Vous ne pouvez pas annuler cette participation.");
        }

        // Vérification du token CSRF
        if ($this->isCsrfTokenValid('cancel' . $participation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($participation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre participation a été annulée avec succès.');
        } else {
            $this->addFlash('error', 'Token invalide, veuillez réessayer.');
        }

        return $this->redirectToRoute('app_participation_front_index');
    }

    // ----------------- BACK -----------------

    /*#[Route('/back', name: 'app_participation_back_index', methods: ['GET'])]
    public function indexBack(ParticipationRepository $participationRepository, EntityManagerInterface $entityManager): Response
    {

        $participations = $entityManager->getRepository(Participation::class)
            ->findAllWithUsersAndFormations(); // À créer dans votre Repository
        return $this->render('participation/back/index.html.twig', [
            'participations' => $participations,
        ]);

        /*$participations = $entityManager->getRepository(Participation::class)->findAll();

        return $this->render('participation/back/index.html.twig', [
            'participations' => $participations,
        ]);
    }*/


    #[Route('/back', name: 'app_participation_back_index', methods: ['GET'])]
    public function indexBack(EntityManagerInterface $entityManager): Response
    {
        // On récupère le repository en précisant le type
        $repository = $entityManager->getRepository(Participation::class);

        // On vérifie que la méthode existe avant de l'appeler
        if (method_exists($repository, 'findAllWithRelations')) {
            $participations = $repository->findAllWithRelations();
            // Ajoutez ce debug temporaire
            dd($participations); // Affiche et arrête l'exécution
        } else {
            // Fallback si la méthode n'existe pas
            $participations = $repository->findAll();
        }

        return $this->render('participation/back/index.html.twig', [
            'participations' => $participations,
        ]);
    }

    #[Route('/back/{id}', name: 'app_participation_back_show', methods: ['GET'])]
    public function showBack(Participation $participation): Response
    {
        return $this->render('participation/back/show.html.twig', [
            'participation' => $participation,
        ]);
    }

    #[Route('/back/{id}/edit', name: 'app_participation_back_edit', methods: ['GET', 'POST'])]
    public function editBack(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(\App\Form\ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_participation_back_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('participation/back/edit.html.twig', [
            'participation' => $participation,
            'form' => $form,
        ]);
    }

    #[Route('/back/{id}/delete', name: 'app_participation_back_delete', methods: ['POST'])]
    public function deleteBack(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $participation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($participation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participation_back_index', [], Response::HTTP_SEE_OTHER);
    }
}
