<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Offre;
use App\Form\CandidatureType;
use App\Repository\CandidatureRepository;
use App\Repository\OffreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/candidatureBack')]
final class CandidatureBackController extends AbstractController
{
    #[Route(name: 'app_candidatureBack_index', methods: ['GET'])]
    public function index(CandidatureRepository $candidatureRepository): Response
    {
        return $this->render('candidatureBack/index.html.twig', [
            'candidatures' => $candidatureRepository->findAll(),
        ]);
    }
    #[Route('/{idCandidature}', name: 'app_candidatureBack_show', methods: ['GET'])]
    public function show(Candidature $candidature): Response
    {
        return $this->render('candidatureBack/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }

    #[Route('/{idCandidature}/edit', name: 'app_candidatureBack_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        // Sauvegarder les noms des fichiers existants
        $existingCv = $candidature->getCv();
        $existingLettre = $candidature->getLettreMotivation();
        $existingDescription = $candidature->getDescription();
    
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
             // Mettre à jour la date de modification seulement si c'est une vraie modification
            if ($form->getData() != $form->getNormData()) {
                $candidature->setDateModification(new \DateTime());
            }
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
            } else {
                // Conserver l'ancien fichier CV si aucun nouveau n'est téléchargé
                $candidature->setCv($existingCv);
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
            } else {
                // Conserver l'ancienne lettre de motivation si aucune nouvelle n'est téléchargée
                $candidature->setLettreMotivation($existingLettre);
            }
            $hasChanged = false;

        // Vérifier chaque champ pour détecter les modifications
        if ($form->get('cv')->getData() !== null) {
            $hasChanged = true;
            // Traitement du CV...
        }

        if ($form->get('lettreMotivation')->getData() !== null) {
            $hasChanged = true;
            // Traitement de la lettre...
        }

        if ($candidature->getDescription() !== $existingDescription) {
            $hasChanged = true;
        }

        if ($hasChanged) {
            $candidature->setDateModification(new \DateTime());
        }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_candidatureBack_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('candidatureBack/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form,
        ]);
    }
}
