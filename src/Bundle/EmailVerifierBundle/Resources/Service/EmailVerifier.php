<?php

namespace App\Bundle\EmailVerifierBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

class EmailVerifier implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    private const API_ENDPOINT = 'https://emailvalidation.abstractapi.com/v1';

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        #[\SensitiveParameter] string $apiKey
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->apiKey = $apiKey;
    }

    public function verify(string $email): array
    {
        try {
            $response = $this->httpClient->request('GET', self::API_ENDPOINT, [
                'query' => [
                    'api_key' => $this->apiKey,
                    'email' => $email
                ],
                'timeout' => 3.0
            ]);

            $data = $response->toArray();

            return [
                'is_valid' => ($data['deliverability'] ?? '') === 'DELIVERABLE',
                'quality_score' => $data['quality_score'] ?? null,
                'details' => $data
            ];

        } catch (TransportExceptionInterface $e) {
            $this->logger->error('API request failed', [
                'error' => $e->getMessage(),
                'email' => $email,
                'api' => 'AbstractAPI'
            ]);
            return ['is_valid' => false, 'error' => 'API unreachable'];
        } catch (\Exception $e) {
            $this->logger->error('Email verification failed', [
                'error' => $e->getMessage(),
                'email' => $email,
                'trace' => $e->getTraceAsString()
            ]);
            return ['is_valid' => false, 'error' => 'Verification failed'];
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            HttpClientInterface::class => HttpClientInterface::class,
            LoggerInterface::class => LoggerInterface::class,
        ];
    }
}
