<?php
// GrammarCheckerService.php - Version améliorée

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GrammarCheckerService
{
    private $client;
    private $logger;
    private $apiUrl;
    private $apiKey;
    private $apiHost;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
        $this->apiUrl = 'https://grammer-checker1.p.rapidapi.com/v1/grammer-checker';
        $this->apiKey = 'f9a37674aemsh1dfa3f25c817e70p173399jsna7ba37200cbd';
        $this->apiHost = 'grammer-checker1.p.rapidapi.com';
    }

    public function checkGrammar(string $text): array
    {
        // Normaliser l'encodage du texte
        $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
        $this->logger->info('Début de la vérification grammaticale', ['text' => $text]);
    
        // Vérification manuelle des erreurs évidentes
        $preCheckErrors = $this->preCheckGrammar($text);
        // On peut logger ce que trouve preCheckGrammar, mais NE PAS retourner tout de suite
        $this->logger->info('Erreurs détectées en pré-vérification', [
            'errors_count' => count($preCheckErrors['errors']['details']),
            'correction' => $preCheckErrors['errors']['correction']
        ]);
        // Continuer à appeler l'API malgré tout
        
        try {
            $requestPayload = [
                'text' => $text,
                'language' => 'fr',
                'strictness' => 'strict'
            ];
    
            $this->logger->debug('Envoi de la requête à l\'API', [
                'url' => $this->apiUrl,
                'headers' => [
                    'X-RapidAPI-Key' => '***'.substr($this->apiKey, -4),
                    'X-RapidAPI-Host' => $this->apiHost
                ],
                'payload' => $requestPayload,
                'text_length' => mb_strlen($text)
            ]);
    
            $response = $this->client->request('POST', $this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-RapidAPI-Key' => $this->apiKey,
                    'X-RapidAPI-Host' => $this->apiHost
                ],
                'json' => $requestPayload,
                'timeout' => 10
            ]);
    
            $statusCode = $response->getStatusCode();
            $responseHeaders = $response->getHeaders();
            $responseContent = $response->getContent(false);
    
            $this->logger->debug('Réponse de l\'API', [
                'status' => $statusCode,
                'headers' => $responseHeaders,
                'response_time' => $responseHeaders['x-response-time'][0] ?? null,
                'content_length' => strlen($responseContent)
            ]);
    
            if ($statusCode !== 200) {
                $this->logger->error('Erreur de l\'API', [
                    'status' => $statusCode,
                    'content' => $responseContent,
                    'request_payload' => $requestPayload
                ]);
                throw new \RuntimeException("Erreur API: statut $statusCode");
            }
    
            // Log du contenu brut avant traitement
            $this->logger->debug('Contenu brut de la réponse API', ['content' => $responseContent]);
    
            $result = $this->processApiResponse($response, $text);
            
            $this->logger->info('Résultat de la vérification', [
                'errors_count' => count($result['errors']['details'] ?? []),
                'original_length' => mb_strlen($text),
                'corrected_length' => mb_strlen($result['errors']['correction'] ?? ''),
                'correction_ratio' => $result['errors']['details'] ? 
                    round(count($result['errors']['details'])/mb_strlen($text)*100, 2).'%' : '0%'
            ]);
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la vérification grammaticale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'text_sample' => mb_substr($text, 0, 50).(mb_strlen($text) > 50 ? '...' : '')
            ]);
            
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
        '/\bvue\b/i' => 'vu',
        '/\bdes personne\b/i' => 'des personnes',
        '/\btres\b/i' => 'très',
        '/\bmotive\b/i' => 'motivées',
        '/\btravaille\b/i' => 'travaillent',
        '/\bequipe\b/i' => 'équipe'
    ];

    $errors = [];
    $details = [];
    $correctedText = $text;

    foreach ($commonErrors as $pattern => $correction) {
        if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE)) {
            $error = $matches[0][0];
            $offset = $matches[0][1];
            $errors[] = "Faute: '$error' devrait être '$correction'";
            $details[] = [
                'message' => "Faute courante détectée",
                'context' => $text,
                'offset' => $offset,
                'length' => strlen($error),
                'rule' => 'Orthographe/Accord',
                'replacements' => [$correction]
            ];
            $correctedText = preg_replace($pattern, $correction, $correctedText);
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
    $content = $response->getContent(false);
    $this->logger->debug('Contenu de la réponse API', ['content' => $content]);

    $data = json_decode($content, true);

    if (!isset($data['errors']['correction']) || !isset($data['errors']['error'])) {
        return $this->postCheckGrammar($originalText);
    }

    $correctedText = $data['errors']['correction'];
    $rawErrors = $data['errors']['error'];

    // Extraction simplifiée des erreurs individuelles depuis le message brut (optionnel)
    preg_match_all('/\d+\.\s+"([^"]+)" devrait être "([^"]+)"/u', $rawErrors, $matches, PREG_SET_ORDER);

    $details = [];
    foreach ($matches as $match) {
        $details[] = [
            'message' => "Correction suggérée : {$match[1]} → {$match[2]}",
            'context' => $originalText,
            'offset' => mb_strpos($originalText, $match[1]),
            'length' => mb_strlen($match[1]),
            'rule' => 'Orthographe/Grammaire',
            'replacements' => [$match[2]]
        ];
    }

    return [
        'errors' => [
            'error' => $rawErrors,
            'correction' => $correctedText,
            'details' => $details
        ]
    ];
}


    private function postCheckGrammar(string $text): array
{
    $errors = [];
    $details = [];
    
    // Vérification des accords
    if (preg_match('/\bdes (\w+[^s])\b/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
        $error = $matches[0][0];
        $offset = $matches[0][1];
        $word = $matches[1][0];
        $errors[] = "Possible erreur de pluriel après 'des' : '$word'";
        $details[] = [
            'message' => "Les noms après 'des' doivent généralement être au pluriel",
            'context' => $text,
            'offset' => $offset,
            'length' => strlen($error),
            'rule' => 'Accord pluriel',
            'replacements' => ['des '.$word.'s']
        ];
    }
    
    // Vérification des participes passés avec 'ai'
    if (preg_match('/\bj\'ai (\w+)(é|e|és|ées)\b/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
        // Règles plus complexes pour les participes passés...
    }

    if (!empty($errors)) {
        return [
            'errors' => [
                'error' => implode("\n", $errors),
                'correction' => $text, // On ne corrige pas automatiquement ici
                'details' => $details
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