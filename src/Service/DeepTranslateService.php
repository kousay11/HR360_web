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

        // ✅ Adaptation à la structure de réponse réelle
        if (!isset($data['data']['translations']['translatedText'][0])) {
            error_log('Réponse API inattendue: ' . print_r($data, true));
            throw new \RuntimeException('Structure de réponse API invalide');
        }

        return (string) $data['data']['translations']['translatedText'][0];

    } catch (\Exception $e) {
        error_log('DeepTranslate API Error: ' . $e->getMessage());
        throw new \RuntimeException('Service de traduction temporairement indisponible');
    }
}

}