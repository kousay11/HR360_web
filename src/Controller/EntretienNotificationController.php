<?php


// src/Controller/EntretienNotificationController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntretienNotificationController extends AbstractController
{
    #[Route('/clear-entretien-notifications', name: 'clear_entretien_notifications')]
    public function clearNotifications(): Response
    {
        $filePath = $this->getParameter('kernel.project_dir').'/var/storage/entretien_notifications.json';
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        return $this->redirectToRoute('app_entretien_index');
    }
    #[Route('/notifications/json', name: 'notifications_json')]
    public function getJson(): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/var/storage/notifications.json';
    
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier non trouvé.');
        }
    
        $json = file_get_contents($filePath);
        return new Response($json, 200, ['Content-Type' => 'application/json']);
    }
    #[Route('/entretien-notifications/widget', name: 'entretien_notifications_widget')]
    public function notificationWidget(ParameterBagInterface $params): Response
    {
        $notifications = [];
        $notificationFile = $params->get('kernel.project_dir').'/var/storage/entretien_notifications.json';
        
        if (file_exists($notificationFile)) {
            $notifications = json_decode(file_get_contents($notificationFile), true) ?: [];
        }

        return $this->render('entretien/notification_widget.html.twig', [
            'notifications' => $notifications
        ]);
    }

    #[Route('/entretien-notifications/count', name: 'entretien_notifications_count')]
public function notificationCount(ParameterBagInterface $params): Response
{
    $notificationFile = $params->get('kernel.project_dir').'/var/storage/entretien_notifications.json';
    
    if (!file_exists($notificationFile)) {
        return new Response('0');
    }

    $json = file_get_contents($notificationFile);
    $notifications = json_decode($json, true);
    
    return new Response((string) (is_array($notifications) ? count($notifications) : 0));
}
}