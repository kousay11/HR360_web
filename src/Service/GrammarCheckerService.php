<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GrammarCheckerService
{
    private $client;
    private $apiUrl;
    private $apiKey;
    private $apiHost;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiUrl = 'https://api.languagetool.org/v2/check';
        $this->apiKey = ''; // LanguageTool est gratuit sans clé pour un usage limité
    }

    public function checkGrammar(string $text): array
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ],
                'body' => [
                    'text' => $text,
                    'language' => 'fr',
                    'enabledOnly' => 'false'
                ],
                'timeout' => 10
            ]);

            $content = $response->toArray();
            
            // Traitement des résultats de LanguageTool
            if (isset($content['matches'])) {
                $errors = [];
                $corrections = [];
                
                foreach ($content['matches'] as $match) {
                    $errors[] = $match['message'];
                    if (isset($match['replacements'][0])) {
                        $corrections[] = $match['replacements'][0]['value'];
                    }
                }
                
                return [
                    'errors' => [
                        'error' => implode("\n", $errors),
                        'correction' => !empty($corrections) ? implode("\n", $corrections) : null
                    ]
                ];
            }
            
            return ['errors' => ['error' => null, 'correction' => null]];
            
        } catch (\Exception $e) {
            return [
                'errors' => [
                    'error' => 'Service indisponible: '.$e->getMessage(),
                    'correction' => null
                ]
            ];
        }
    }
}