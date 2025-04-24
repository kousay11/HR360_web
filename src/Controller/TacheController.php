<?php

namespace App\Controller;

use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Projet;
use App\Enum\StatusTache;
use App\Repository\ProjetRepository;
use App\Service\TrelloApiService;

#[Route('/tache')]
final class TacheController extends AbstractController
{
    #[Route('/', name: 'app_tache_index', methods: ['GET'])]
    #[Route('/project/{id}', name: 'app_tache_by_project', methods: ['GET'])]
    public function index(Request $request, ?Projet $projet, TacheRepository $tacheRepository,TrelloApiService $trello_api_service,EntityManagerInterface $entityManager): Response
    {
        $query = $request->query->get('q', '');
        
        if ($projet) {
            $taches = $query 
                ? $tacheRepository->searchByProject($projet, $query)
                : $tacheRepository->findBy(['projet' => $projet]);
        } else {
            $taches = $query 
                ? $tacheRepository->search($query)
                : $tacheRepository->findAll();
        }

        foreach ($taches as $tache) {
            if ($tache->getTrelloboardid() !== null) {
                if ($trello_api_service->isBoardStillOnTrello($tache->getTrelloboardid()) === false) {
                    $tache->setTrelloboardid(null);
                    $entityManager->flush();    
                }
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('tache/_task_list.html.twig', [
                'taches' => $taches,
                'projet' => $projet
            ]);
        }

        return $this->render('tache/index.html.twig', [
            'taches' => $taches,
            'projet' => $projet
        ]);
    }


    #[Route('/search/project/{id}', name: 'app_tache_search_project', methods: ['GET'])]
    #[Route('/search', name: 'app_tache_search', methods: ['GET'])]
    public function search(Request $request, ?Projet $projet, TacheRepository $tacheRepository): Response
    {
        $query = $request->query->get('q', '');
        
        $taches = $projet 
            ? $tacheRepository->searchByProject($projet, $query)
            : $tacheRepository->search($query);

        return $this->render('tache/_task_list.html.twig', [
            'taches' => $taches,
            'projet' => $projet
        ]);
    }

    #[Route('/prioritize/project/{id}', name: 'app_tache_prioritize_project')]
#[Route('/prioritize', name: 'app_tache_prioritize')]
public function prioritize(Request $request, ?Projet $projet, TacheRepository $tacheRepository): Response
{
    $taches = $projet 
        ? $tacheRepository->prioritizeByProject($projet)
        : $tacheRepository->prioritize();

    if ($request->isXmlHttpRequest()) {
        return $this->render('tache/_task_list.html.twig', [
            'taches' => $taches,
            'projet' => $projet
        ]);
    }

    return $this->render('tache/index.html.twig', [
        'taches' => $taches,
        'projet' => $projet
    ]);
}

    #[Route('/new/{projetId}', name: 'app_tache_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProjetRepository $projetRepository, ?int $projetId = null): Response
    {
        $tache = new Tache();
        $tache->setStatut(StatusTache::AFAIRE);
        $projet = $projetId ? $projetRepository->find($projetId) : null;
        
        if ($projetId) {
            $projet = $entityManager->getRepository(Projet::class)->find($projetId);
            if ($projet) {
                $tache->setProjet($projet);
            }
        }

        $form = $this->createForm(TacheType::class, $tache, [
            'projet' => $projet,
            'is_edit' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tache);
            $entityManager->flush();

            $redirectRoute = $projetId 
                ? 'app_tache_by_project' 
                : 'app_tache_index';
                
            return $this->redirectToRoute($redirectRoute, [
                'id' => $projetId
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tache/new.html.twig', [
            'tache' => $tache,
            'form' => $form,
            'projetId' => $projetId
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tache_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tache $tache, EntityManagerInterface $entityManager): Response
    {
        $projetId = $tache->getProjet() ? $tache->getProjet()->getId() : null;
        
        $form = $this->createForm(TacheType::class, $tache, [
            'is_edit' => true,
            'projet' => $tache->getProjet()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $redirectRoute = $projetId 
                ? 'app_tache_by_project' 
                : 'app_tache_index';
                
            return $this->redirectToRoute($redirectRoute, [
                'id' => $projetId
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tache/edit.html.twig', [
            'tache' => $tache,
            'form' => $form,
            'projetId' => $projetId
        ]);
    }

    #[Route('/{id}', name: 'app_tache_delete', methods: ['POST'])]
    public function delete(Request $request, Tache $tache, EntityManagerInterface $entityManager,TrelloApiService $trello_api_service): Response
    {
        $projetId = $tache->getProjet() ? $tache->getProjet()->getId() : null;
        
        if ($this->isCsrfTokenValid('delete'.$tache->getId(), $request->getPayload()->getString('_token'))) {
            if ($tache->getTrelloboardid() !== null) {
                $trello_api_service->deleteBoard($tache->getTrelloboardid());
            }
            $entityManager->remove($tache);
            $entityManager->flush();
        }

        $redirectRoute = $projetId 
            ? 'app_tache_by_project' 
            : 'app_tache_index';
            
        return $this->redirectToRoute($redirectRoute, [
            'id' => $projetId
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_tache_show', methods: ['GET'])]
    public function show(Tache $tache): Response
    {
        $projetId = $tache->getProjet() ? $tache->getProjet()->getId() : null;
        return $this->render('tache/show.html.twig', [
            'tache' => $tache,
            'projetId' => $projetId
        ]);
    }

    #[Route('/export/project/{id}', name: 'app_tache_export_project')]
#[Route('/export', name: 'app_tache_export')]
public function export(?Projet $projet, TacheRepository $tacheRepository): Response
{
    $taches = $tacheRepository->findAllForExport($projet);

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set project name with blue background
    if ($projet) {
        $sheet->setCellValue('A1', 'Project: ' . $projet->getNom());
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '0070C0']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);
    }

    // Set headers
    $headers = ['Task Name', 'Description', 'Start Date', 'End Date', 'Status'];
    $sheet->fromArray($headers, null, 'A2');
    $sheet->getStyle('A2:E2')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'color' => ['rgb' => 'D9D9D9']
        ]
    ]);
    $sheet->freezePane('A3');
    $sheet->setAutoFilter('A2:E2');
    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(25); // Task Name
    $sheet->getColumnDimension('B')->setWidth(40); // Description (wider for wrapped text)
    $sheet->getColumnDimension('C')->setWidth(15); // Start Date
    $sheet->getColumnDimension('D')->setWidth(15); // End Date
    $sheet->getColumnDimension('E')->setWidth(15); // Status

    // Add tasks data
    $row = 3;
    foreach ($taches as $tache) {
        $sheet->setCellValue('A' . $row, $tache->getNom());
        
        // Shorten description and add ellipsis if too long
        $description = $tache->getDescription();
        $maxLength = 200; // Adjust as needed
        if (strlen($description) > $maxLength) {
            $description = substr($description, 0, $maxLength) . '...';
        }
        
        $sheet->setCellValue('B' . $row, $description);
        $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        
        $sheet->setCellValue('C' . $row, $tache->getDateDebut() ? $tache->getDateDebut()->format('Y-m-d') : '');
        $sheet->setCellValue('D' . $row, $tache->getDateFin() ? $tache->getDateFin()->format('Y-m-d') : '');
        $sheet->setCellValue('E' . $row, $tache->getStatut()->value);

        // Color status cells based on value
        $statusColor = match($tache->getStatut()->value) {
            'A faire' => 'FF0000', // Red
            'En cours' => 'FFA500', // Orange
            'Terminée' => '00B050', // Green
            default => '000000'
        };

        $sheet->getStyle('E' . $row)->applyFromArray([
            'font' => [
                'color' => ['rgb' => $statusColor]
            ]
        ]);

        // Set row height for description (auto-size will adjust this)
        $sheet->getRowDimension($row)->setRowHeight(-1); // Auto-size

        $row++;
    }

    // Create Excel file
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    $response = new Response();
    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment;filename="tasks_export_' . ($projet ? $projet->getNom() : 'all') . '.xlsx"');
    $response->headers->set('Cache-Control', 'max-age=0');
    
    ob_start();
    $writer->save('php://output');
    $response->setContent(ob_get_clean());
    
    return $response;
}
}