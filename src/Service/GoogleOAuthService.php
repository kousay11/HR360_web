<?php
// src/Service/GoogleOAuthService.php

namespace App\Service;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\EntryPoint;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleOAuthService
{
    private $client;
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->client = new Client();
        $this->initializeClient();
    }

    private function initializeClient(): void
    {
        $credentialsPath = $this->params->get('kernel.project_dir').'/config/google/credentials.json';
        $tokenPath = $this->params->get('kernel.project_dir').'/config/google/token.json';

        $this->client->setApplicationName('Intellij');
        $this->client->setScopes([Calendar::CALENDAR, Calendar::CALENDAR_EVENTS]);
        $this->client->setAuthConfig($credentialsPath);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        // Charger le token s'il existe 
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);
        }
    }

    public function getClient(): Client
    {
        // Si le token est expiré, on le rafraîchit
        if ($this->client->isAccessTokenExpired()) {
            if ($this->client->getRefreshToken()) {
                $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                // Sauvegarder le nouveau token
                file_put_contents(
                    $this->params->get('kernel.project_dir').'/config/google/token.json',
                    json_encode($this->client->getAccessToken())
                );
            } else {
                // Rediriger vers l'authentification
                $authUrl = $this->client->createAuthUrl();
                header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
                exit;
            }
        }
        return $this->client;
    }

    public function getCalendarService(): Calendar
    {
        return new Calendar($this->getClient());
    }

    public function exchangeCode(string $code): void
{
    $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);
    if (array_key_exists('error', $accessToken)) {
        throw new \Exception("Erreur lors de l'obtention du token : " . $accessToken['error']);
    }

    file_put_contents(
        $this->params->get('kernel.project_dir').'/config/google/token.json',
        json_encode($accessToken)
    );
}

}