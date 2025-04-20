<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Psr\Log\LoggerInterface;
use \DateTime;

class TrelloApiService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $apiToken;
    private string $trelloUrl;
    private LoggerInterface $logger;

    public function __construct(
        ParameterBagInterface $params,
        LoggerInterface $logger
    ) {
        $this->client = HttpClient::create();
        $this->apiKey = $params->get('trello_api_key');
        $this->apiToken = $params->get('trello_api_token');
        $this->trelloUrl = 'https://api.trello.com/1';
        $this->logger = $logger;
    }

    public function createBoardWithLists(string $boardName, DateTime $dateDebut, DateTime $dateFin): ?string
    {
        // Step 1: Create the Trello Board
        $boardId = $this->createBoard($boardName);
        if ($boardId === null) {
            $this->logger->error('Board creation failed!');
            return null;
        }

        // Step 2: Generate lists for each day in the range
        $currentDate = clone $dateDebut;
        while ($currentDate <= $dateFin) {
            $formattedDate = $this->formatDate($currentDate);
            $this->createList($boardId, $formattedDate);
            $currentDate->modify('+1 day');
        }

        return $boardId;
    }

    private function createBoard(string $boardName): ?string
    {
        $url = $this->trelloUrl . '/boards/';
        
        try {
            $response = $this->client->request('POST', $url, [
                'query' => [
                    'name' => $boardName,
                    'key' => $this->apiKey,
                    'token' => $this->apiToken,
                    'defaultLists' => 'false',
                    'prefs_permissionLevel' => 'public'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                return $content['id'];
            }
            
            $this->logger->error('Error creating Trello board', ['response' => $response->getContent(false)]);
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error('Exception when creating Trello board', ['exception' => $e->getMessage()]);
            return null;
        }
    }

    private function createList(string $boardId, string $listName): void
    {
        $url = $this->trelloUrl . '/lists';
        
        try {
            $response = $this->client->request('POST', $url, [
                'query' => [
                    'name' => $listName,
                    'idBoard' => $boardId,
                    'key' => $this->apiKey,
                    'token' => $this->apiToken
                ]
            ]);

            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Error creating list', ['response' => $response->getContent(false)]);
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Exception when creating Trello list', ['exception' => $e->getMessage()]);
        }
    }

    private function formatDate(DateTime $date): string
{
    // Try using IntlDateFormatter if available
    if (class_exists('IntlDateFormatter')) {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::NONE,
            null,
            null,
            'EEEE – dd/MM/yyyy'
        );
        
        return '🗓️ ' . $formatter->format($date);
    }
    
    // Fallback to DateTime format
    $days = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $dayName = $days[(int)$date->format('w')];
    
    return '🗓️ ' . $dayName . ' – ' . $date->format('d/m/Y');
}


    public function addMembersToBoard(string $boardId, array $memberEmails): bool
    {
        foreach ($memberEmails as $email) {
            $url = $this->trelloUrl . '/boards/' . $boardId . '/members';
        
            try {
                $response = $this->client->request('PUT', $url, [
                    'query' => [
                        'email' => $email,
                        'key' => $this->apiKey,
                        'token' => $this->apiToken,
                        'type' => 'normal'
                    ]
                ]);
    
                if ($response->getStatusCode() !== 200) {
                    return false;
                }
                
            } catch (\Exception $e) {
                $this->logger->error('Exception when adding member to board', [
                    'exception' => $e->getMessage(),
                    'boardId' => $boardId,
                    'email' => $email
                ]);
                return false;
            }
        }
        return true;
    }

    public function deleteBoard(string $boardId): bool
    {
        $url = $this->trelloUrl . '/boards/' . $boardId;
        
        try {
            $response = $this->client->request('DELETE', $url, [
                'query' => [
                    'key' => $this->apiKey,
                    'token' => $this->apiToken
                ]
            ]);

            return $response->getStatusCode() === 200;
            
        } catch (\Exception $e) {
            $this->logger->error('Exception when deleting board', [
                'exception' => $e->getMessage(),
                'boardId' => $boardId
            ]);
            return false;
        }
    }

    public function isBoardStillOnTrello(string $boardId): bool
    {
        $url = $this->trelloUrl . '/boards/' . $boardId;
        
        try {
            $response = $this->client->request('GET', $url, [
                'query' => [
                    'key' => $this->apiKey,
                    'token' => $this->apiToken
                ]
            ]);

            $statusCode = $response->getStatusCode();
            $this->logger->info('Checking board status', [
                'boardId' => $boardId,
                'status' => $statusCode
            ]);

            if ($statusCode === 404) {
                $this->logger->info('Board was deleted', ['boardId' => $boardId]);
                return false;
            }

            if ($statusCode === 200) {
                $this->logger->info('Board still exists', ['boardId' => $boardId]);
                return true;
            }

            $this->logger->warning('Unexpected response from Trello API', ['status' => $statusCode]);
            return true;
            
        } catch (\Exception $e) {
            $this->logger->error('Exception when checking board status', [
                'exception' => $e->getMessage(),
                'boardId' => $boardId
            ]);
            return true;
        }
    }

    public function removeMemberFromBoard(string $boardId, string $memberId): bool
    {
        $url = $this->trelloUrl . '/boards/' . $boardId . '/members/' . $memberId;
        
        try {
            $response = $this->client->request('DELETE', $url, [
                'query' => [
                    'key' => $this->apiKey,
                    'token' => $this->apiToken
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $this->logger->info('Member removed from board', [
                    'memberId' => $memberId,
                    'boardId' => $boardId
                ]);
                return true;
            }
            
            $this->logger->error('Failed to remove member from board', [
                'response' => $response->getContent(false),
                'memberId' => $memberId,
                'boardId' => $boardId
            ]);
            return false;
            
        } catch (\Exception $e) {
            $this->logger->error('Exception when removing member from board', [
                'exception' => $e->getMessage(),
                'memberId' => $memberId,
                'boardId' => $boardId
            ]);
            return false;
        }
    }

    public function getMemberIdByEmail(string $email): ?string
    {
        $url = $this->trelloUrl . '/members/' . $email;
        
        try {
            $response = $this->client->request('GET', $url, [
                'query' => [
                    'key' => $this->apiKey,
                    'token' => $this->apiToken
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $content = $response->toArray();
                return $content['id'];
            }
            
            $this->logger->error('Failed to find member ID for email', [
                'email' => $email,
                'response' => $response->getContent(false)
            ]);
            return null;
            
        } catch (\Exception $e) {
            $this->logger->error('Exception when getting member ID by email', [
                'exception' => $e->getMessage(),
                'email' => $email
            ]);
            return null;
        }
    }

    public function removeMemberFromBoardByEmail(string $boardId, string $email): bool
    {
        $memberId = $this->getMemberIdByEmail($email);
        if ($memberId === null) {
            $this->logger->error('Member with email not found', ['email' => $email]);
            return false;
        }
        
        return $this->removeMemberFromBoard($boardId, $memberId);
    }
}