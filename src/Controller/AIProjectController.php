<?php
namespace App\Controller;

use App\Service\AIProjectGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AIProjectGeneratorType;
use App\Entity\Tache;
use App\Entity\Projet;
use App\Enum\StatusTache;
use Doctrine\ORM\EntityManagerInterface;


class AIProjectController extends AbstractController
{
    // Update the generate method in AIProjectController
#[Route('/projet/ai-generate', name: 'app_projet_ai_generate', methods: ['GET', 'POST'])]
public function generate(Request $request, AIProjectGenerator $aiGenerator): Response
{
    $form = $this->createForm(AIProjectGeneratorType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();
        
        try {
            $generatedData = $aiGenerator->generateProject(
                $data['name'],
                $data['start_date'],
                $data['end_date'],
                $data['task_count']
            );
            
            // Store in session for confirmation
            $request->getSession()->set('ai_generated_project', [
                'project' => [
                    'name' => $generatedData['projectName'],
                    'description' => $generatedData['description'],
                    'start_date' => $generatedData['startDate'],
                    'end_date' => $generatedData['endDate'],
                ],
                'tasks' => $generatedData['tasks']
            ]);

            return $this->redirectToRoute('app_projet_ai_confirm');
        } catch (\Exception $e) {
            $this->addFlash('error', 'AI generation failed: ' . $e->getMessage());
        }
    }

    return $this->render('projet/ai_generate.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/projet/ai-confirm', name: 'app_projet_ai_confirm', methods: ['GET', 'POST'])]
public function confirm(Request $request, EntityManagerInterface $entityManager): Response
{
    $session = $request->getSession();
    $generatedData = $session->get('ai_generated_project');
    
    if (!$generatedData) {
        return $this->redirectToRoute('app_projet_ai_generate');
    }

    if ($request->isMethod('POST')) {
        $postData = $request->request->all();
        
        // Create and save the project
        $project = new Projet();
        $project->setNom($postData['project']['name']);
        $project->setDescription($postData['project']['description']);
        $project->setDateDebut(new \DateTime($postData['project']['start_date']));
        $project->setDateFin(new \DateTime($postData['project']['end_date']));
        
        $entityManager->persist($project);
        $entityManager->flush();
        
        // Save all tasks, not just the first one
        foreach ($generatedData['tasks'] as $taskIndex => $taskData) {
            $task = new Tache();
            $task->setNom($postData['tasks'][$taskIndex]['name']);
            $task->setDescription($postData['tasks'][$taskIndex]['description']);
            $task->setDateDebut(new \DateTime($postData['tasks'][$taskIndex]['start_date']));
            $task->setDateFin(new \DateTime($postData['tasks'][$taskIndex]['end_date']));
            $task->setStatut(StatusTache::AFAIRE);
            $task->setProjet($project);
            
            $entityManager->persist($task);
        }
        
        $entityManager->flush();
        
        // Clear session data
        $session->remove('ai_generated_project');
        
        $this->addFlash('success', 'Project and tasks created successfully!');
        return $this->redirectToRoute('app_projet_show', ['id' => $project->getId()]);
    }

    return $this->render('projet/ai_confirm.html.twig', [
        'project' => $generatedData['project'],
        'tasks' => $generatedData['tasks'],
    ]);
}
}