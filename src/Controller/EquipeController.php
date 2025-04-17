<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Form\EquipeType;
use App\Repository\EquipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Equipeemploye;
use App\Form\EquipeemployeType;
use App\Entity\Utilisateur;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    #[Route(name: 'app_equipe_index', methods: ['GET'])]
    public function index(EquipeRepository $equipeRepository): Response
    {
        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipeRepository->findAll(),
        ]);
    }
    #[Route('/search', name: 'app_equipe_search', methods: ['GET'])]
    public function search(Request $request, EquipeRepository $equipeRepository): Response
    {
        $query = $request->query->get('q', '');
        $equipes = $equipeRepository->search($query);

        if ($request->isXmlHttpRequest()) {
            return $this->render('equipe/_grid.html.twig', [
                'equipes' => $equipes,
            ]);
        }

        return $this->render('equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }

    #[Route('/new', name: 'app_equipe_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipe = new Equipe();
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/new.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_show', methods: ['GET'])]
    public function show(Equipe $equipe): Response
    {
        return $this->render('equipe/show.html.twig', [
            'equipe' => $equipe,
        ]);
    }

    #[Route('/{id}/add-member', name: 'app_equipeemploye_new', methods: ['GET','POST'])]
    public function newMember(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
{
    $equipeEmploye = new Equipeemploye();
    $equipeEmploye->setEquipe($equipe); // Set the team ID
    
    $form = $this->createForm(EquipeemployeType::class, $equipeEmploye, ['equipe' => $equipe]);
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($equipeEmploye);
        $entityManager->flush();

        $this->addFlash('success', 'Member added to team successfully!');
        return $this->redirectToRoute('app_equipe_show', ['id' => $equipe->getId()]);
    }

    return $this->render('equipeemploye/new.html.twig', [
        'equipe' => $equipe,
        'form' => $form->createView(),
    ]);
}
#[Route('/equipe/{id_equipe}/remove-member/{id_employe}', name: 'app_equipeemploye_delete', methods: ['POST'])]
public function deleteMember(Request $request, int $id_equipe, Utilisateur $id_employe, EntityManagerInterface $entityManager): Response
{   $equipe = $entityManager->getRepository(Equipe::class)->find($id_equipe);
    $employe = $entityManager->getRepository(Utilisateur::class)->find($id_employe);
    $equipeEmploye = $entityManager->getRepository(Equipeemploye::class)->findOneBy([
        'equipe' => $equipe,
        'utilisateur' => $employe
    ]);

    if (!$equipeEmploye) {
        throw $this->createNotFoundException('Team member not found');
    }

    if ($this->isCsrfTokenValid('delete'.$id_equipe.$id_employe->getId(), $request->request->get('_token'))) {
        $entityManager->remove($equipeEmploye);
        $entityManager->flush();
        $this->addFlash('success', 'Member removed from team successfully!');
    }

    return $this->redirectToRoute('app_equipe_show', ['id' => $id_equipe]);
}

    #[Route('/{id}/edit', name: 'app_equipe_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EquipeType::class, $equipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('equipe/edit.html.twig', [
            'equipe' => $equipe,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipe_delete', methods: ['POST'])]
    public function delete(Request $request, Equipe $equipe, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipe->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($equipe);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_equipe_index', [], Response::HTTP_SEE_OTHER);
    }
}
