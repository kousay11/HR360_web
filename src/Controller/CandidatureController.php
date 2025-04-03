<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Offre;
use App\Form\CandidatureType;
use App\Form\CandidatureTypeNew;
use App\Repository\CandidatureRepository;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/candidature')]
final class CandidatureController extends AbstractController
{
    #[Route(name: 'app_candidature_index', methods: ['GET'])]
    public function index(CandidatureRepository $candidatureRepository): Response
    {
        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatureRepository->findAll(),
        ]);
    }

    #[Route('/new/{idOffre}', name: 'app_candidature_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, OffreRepository $offreRepository, int $idOffre): Response
    {
        $offre = $offreRepository->find($idOffre);
        
        if (!$offre) {
            throw $this->createNotFoundException('Offre non trouvée');
        }
    
        $candidature = new Candidature();
        $candidature->setOffre($offre);
        $candidature->setDateCandidature(new \DateTime());
        $candidature->setDateModification(new \DateTime());
        $candidature->setStatut('En attente');
        
        // Ajoutez l'ID de l'utilisateur connecté
        
            $candidature->setIduser(1);
           
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier CV
            $cvFile = $form->get('cv')->getData();
            if ($cvFile) {
                $newFilename = uniqid().'.'.$cvFile->guessExtension();
                $cvFile->move(
                    $this->getParameter('cvs_directory'),
                    $newFilename
                );
                $candidature->setCv($newFilename);
            }
    
            // Gestion de la lettre de motivation
            $lettreFile = $form->get('lettreMotivation')->getData();
            if ($lettreFile) {
                $newFilename = uniqid().'.'.$lettreFile->guessExtension();
                $lettreFile->move(
                    $this->getParameter('lettres_directory'),
                    $newFilename
                );
                $candidature->setLettreMotivation($newFilename);
            }
    
            $entityManager->persist($candidature);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('candidature/new.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }
    #[Route('/{idCandidature}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{idCandidature}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        $existingCv = $candidature->getCv();
        $existingLettre = $candidature->getLettreMotivation();
        $existingDescription = $candidature->getDescription();
    
        $form = $this->createForm(CandidatureTypeNew::class, $candidature);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $hasChanged = false;
    
            // Gestion du fichier CV
            $cvFile = $form->get('cv')->getData();
            if ($cvFile) {
                // Supprimer l'ancien fichier CV s'il existe
                if ($existingCv) {
                    $cvPath = $this->getParameter('cvs_directory') . '/' . $existingCv;
                    if (file_exists($cvPath)) {
                        unlink($cvPath);
                    }
                }
                // Enregistrer le nouveau fichier CV
                $newFilename = uniqid().'.'.$cvFile->guessExtension();
                $cvFile->move(
                    $this->getParameter('cvs_directory'),
                    $newFilename
                );
                $candidature->setCv($newFilename);
                $hasChanged = true;
            }
    
            // Gestion de la lettre de motivation
            $lettreFile = $form->get('lettreMotivation')->getData();
            if ($lettreFile) {
                // Supprimer l'ancienne lettre de motivation s'il existe
                if ($existingLettre) {
                    $lettrePath = $this->getParameter('lettres_directory') . '/' . $existingLettre;
                    if (file_exists($lettrePath)) {
                        unlink($lettrePath);
                    }
                }
                // Enregistrer la nouvelle lettre de motivation
                $newFilename = uniqid().'.'.$lettreFile->guessExtension();
                $lettreFile->move(
                    $this->getParameter('lettres_directory'),
                    $newFilename
                );
                $candidature->setLettreMotivation($newFilename);
                $hasChanged = true;
            }
    
            if ($candidature->getDescription() !== $existingDescription) {
                $hasChanged = true;
            }
    
            if ($hasChanged) {
                $candidature->setDateModification(new \DateTime());
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('candidature/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }
    #[Route('/{idCandidature}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidature->getIdCandidature(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
    }
}
