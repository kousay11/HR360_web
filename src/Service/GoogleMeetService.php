<?php
// src/Service/GoogleMeetService.php

namespace App\Service;

use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\EntryPoint;
use DateTimeInterface;

class GoogleMeetService
{
    private $googleOAuthService;

    public function __construct(GoogleOAuthService $googleOAuthService)
    {
        $this->googleOAuthService = $googleOAuthService;
    }

    public function createMeetLink(DateTimeInterface $startDateTime, DateTimeInterface $endDateTime, string $summary = 'Entretien en ligne'): string
    {
        $calendarService = $this->googleOAuthService->getCalendarService();

        // Configurer les dates/heures
        $start = new EventDateTime();
        $start->setDateTime($startDateTime->format('c'));
        $start->setTimeZone('UTC');

        $end = new EventDateTime();
        $end->setDateTime($endDateTime->format('c'));
        $end->setTimeZone('UTC');

        // Configurer la conférence Google Meet
        $conferenceData = new ConferenceData();
        $conferenceData->setCreateRequest(new CreateConferenceRequest([
            'requestId' => 'meet-' . time(),
            'conferenceSolutionKey' => new ConferenceSolutionKey(['type' => 'hangoutsMeet'])
        ]));

        // Créer l'événement
        $event = new Event([
            'summary' => $summary,
            'start' => $start,
            'end' => $end,
            'conferenceData' => $conferenceData
        ]);

        // Ajouter l'email du participant si fourni
       
        // Insérer l'événement dans le calendrier
        $createdEvent = $calendarService->events->insert('primary', $event, [
            'conferenceDataVersion' => 1
        ]);

        // Récupérer le lien Meet
        if ($createdEvent->getConferenceData() && $createdEvent->getConferenceData()->getEntryPoints()) {
            foreach ($createdEvent->getConferenceData()->getEntryPoints() as $entryPoint) {
                if ($entryPoint->getEntryPointType() === 'video') {
                    return $entryPoint->getUri();
                }
            }
        }

        throw new \RuntimeException('Impossible de créer le lien Google Meet');
    }
}