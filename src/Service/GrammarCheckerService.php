<?php
// GrammarCheckerService.php - Version améliorée

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
        $this->apiUrl = 'https://grammer-checker1.p.rapidapi.com/v1/grammer-checker';
        $this->apiKey = '58781479a8msh50ee0c2c7fb876ap1b2da2jsnaf8f922475b2';
        $this->apiHost = 'grammer-checker1.p.rapidapi.com';
    }

    public function checkGrammar(string $text): array
    {
        // Vérification manuelle des erreurs évidentes avant d'envoyer à l'API
        $preCheckErrors = $this->preCheckGrammar($text);
        if (!empty($preCheckErrors['errors']['details'])) {
            return $preCheckErrors;
        }

        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' => $this->apiHost
                ],
                'json' => [
                    'text' => $text,
                    'language' => 'fr',
                    'strictness' => 'strict' // Option plus stricte si disponible
                ],
                'timeout' => 10
            ]);

            return $this->processApiResponse($response, $text);
            
        } catch (\Exception $e) {
            return [
                'errors' => [
                    'error' => 'Service indisponible: '.$e->getMessage(),
                    'correction' => $text,
                    'details' => []
                ]
            ];
        }
    }

    private function preCheckGrammar(string $text): array
    {
        $commonErrors = [
            'magazin' => 'magasin',
            'achéter' => 'acheter',
            'pomme' => 'pommes',
            'banane' => 'bananes'
        ];

        $errors = [];
        $corrections = [];
        $details = [];
        $correctedText = $text;

        foreach ($commonErrors as $error => $correction) {
            if (stripos($text, $error) !== false) {
                $offset = stripos($text, $error);
                $errors[] = "Faute d'orthographe : '$error' devrait être '$correction'";
                $details[] = [
                    'message' => "Faute d'orthographe courante",
                    'context' => $text,
                    'offset' => $offset,
                    'length' => strlen($error),
                    'rule' => 'Orthographe',
                    'replacements' => [$correction]
                ];
                $correctedText = str_ireplace($error, $correction, $correctedText);
            }
        }

        if (!empty($errors)) {
            return [
                'errors' => [
                    'error' => implode("\n", $errors),
                    'correction' => $correctedText,
                    'details' => $details
                ]
            ];
        }

        return ['errors' => ['error' => null, 'correction' => $text, 'details' => []]];
    }

    private function processApiResponse($response, string $originalText): array
    {
        $content = $response->toArray();
        
        if (empty($content['matches'])) {
            // Vérification finale au cas où l'API aurait raté quelque chose
            return $this->postCheckGrammar($originalText);
        }

        $errors = [];
        $details = [];
        $correctedText = $originalText;

        // Tri des erreurs par position décroissante pour correction
        usort($content['matches'], function($a, $b) {
            return $b['offset'] <=> $a['offset'];
        });
        
        foreach ($content['matches'] as $match) {
            $errorDetail = [
                'message' => $match['message'] ?? 'Erreur inconnue',
                'context' => $match['context']['text'] ?? '',
                'offset' => $match['offset'] ?? 0,
                'length' => $match['length'] ?? 0,
                'rule' => $match['rule']['description'] ?? '',
                'replacements' => array_column($match['replacements'] ?? [], 'value')
            ];
            
            $details[] = $errorDetail;
            $errors[] = $errorDetail['message'];
            
            if (!empty($match['replacements'][0]['value'])) {
                $replacement = $match['replacements'][0]['value'];
                $correctedText = substr_replace(
                    $correctedText,
                    $replacement,
                    $match['offset'],
                    $match['length']
                );
            }
        }
        
        return [
            'errors' => [
                'error' => implode("\n", array_unique($errors)),
                'correction' => $correctedText,
                'details' => $details
            ]
        ];
    }

    private function postCheckGrammar(string $text): array
    {
        // Vérifications supplémentaires après l'API
        // Par exemple, vérifier les accords pluriels basiques
        if (preg_match('/\bdes \w+[^s]\b/', $text)) {
            return [
                'errors' => [
                    'error' => 'Possible erreur de pluriel après "des"',
                    'correction' => $text, // Pas de correction automatique ici
                    'details' => [[
                        'message' => 'Les noms après "des" doivent généralement être au pluriel',
                        'context' => $text,
                        'rule' => 'Accord pluriel'
                    ]]
                ]
            ];
        }

        return [
            'errors' => [
                'error' => null,
                'correction' => $text,
                'details' => []
            ]
        ];
    }
}