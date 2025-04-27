<?php


namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class QuizApiService
{
    private const API_KEY = 'vSI5WEVie4xRm1b0UH4EgsvBMWGqUU3ZRNi05I9v';
    private const API_URL = 'https://quizapi.io/api/v1/questions';

    private $httpClient;
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->httpClient = HttpClient::create();
        $this->serializer = $serializer;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function fetchQuizQuestions(string $category, string $difficulty, int $limit): array
{
    $response = $this->httpClient->request('GET', self::API_URL, [
        'query' => [
            'apiKey' => self::API_KEY,
            'limit' => $limit,
            'category' => $category,
            'difficulty' => $difficulty
        ]
    ]);

    $content = $response->toArray();

    // Normalise les données avant désérialisation
    foreach ($content as &$questionData) {
        if (!isset($questionData['description'])) {
            $questionData['description'] = null;
        }
        if (!isset($questionData['explanation'])) {
            $questionData['explanation'] = null;
        }
        if (!isset($questionData['tip'])) {
            $questionData['tip'] = null;
        }
    }

    return $this->serializer->deserialize(
        json_encode($content),
        'App\Entity\QuizQuestion[]',
        'json'
    );
}
}