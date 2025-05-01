<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class GeminiService
{
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $apiUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        #[\SensitiveParameter] string $apiKey
    )
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyDJCHou0OzX0ydCPVVv3my6SXl-hVJULoA';
    }

    public function sendMessage(string $message): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $message]
                            ]
                        ]
                    ]
                ],
                'timeout' => 30
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                return $response->toArray();
            }

            throw new \RuntimeException('La requête API a échoué avec le statut : ' . $response->getStatusCode());
        } catch (\Throwable $e) {
            $this->logger->error('Erreur lors de l\'appel à l\'API Gemini', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
