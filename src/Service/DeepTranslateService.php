<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class DeepTranslateService
{
    private const API_HOST = 'deep-translate1.p.rapidapi.com';
    private const TRANSLATE_URL = 'https://deep-translate1.p.rapidapi.com/language/translate/v2';

    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    ) {}

    public function translate(string $text, string $source = 'fr', string $target = 'en'): string
{
    try {
        $response = $this->client->request('POST', self::TRANSLATE_URL, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-rapidapi-host' => self::API_HOST,
                'x-rapidapi-key' => $this->apiKey,
                'Accept' => 'application/json'
            ],
            'json' => [
                'q' => $text,
                'source' => $source,
                'target' => $target
            ],
            'timeout' => 30
        ]);

        $data = $response->toArray();

        // Vérification approfondie de la structure de réponse
        if (!isset($data['data']['translations']['translatedText'])) {
            error_log('Réponse API inattendue: ' . print_r($data, true));
            throw new \RuntimeException('Structure de réponse API invalide');
        }

        // Retourne explicitement le texte traduit sous forme de string
        return (string) $data['data']['translations']['translatedText'];

    } catch (\Exception $e) {
        error_log('DeepTranslate API Error: ' . $e->getMessage());
        throw new \RuntimeException('Service de traduction temporairement indisponible');
    }
}
}