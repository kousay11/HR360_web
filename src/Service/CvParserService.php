<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CvParserService
{
    private HttpClientInterface $client;
    private LoggerInterface $logger;
    private string $apiKey;
    private ParameterBagInterface $params;

    public function __construct(
        HttpClientInterface $client, 
        LoggerInterface $logger,
        ParameterBagInterface $params
    ) {
        $this->client = $client;
        $this->logger = $logger;
        $this->params = $params;
        $this->apiKey = 'f9a37674aemsh1dfa3f25c817e70p173399jsna7ba37200cbd';
    }

    public function parseCv(string $cvFileName): array
    {
        try {
            // Chemin complet du fichier
            $cvPath = $this->params->get('kernel.project_dir') . '/public/uploads/cvs/' . $cvFileName;
            
            // Vérification que le fichier existe
            if (!file_exists($cvPath)) {
                throw new \Exception("Le fichier CV n'existe pas: $cvPath");
            }

            // Vérification du type MIME
            $mimeType = mime_content_type($cvPath);
            if ($mimeType !== 'application/pdf') {
                throw new \Exception("Le fichier doit être un PDF (type détecté: $mimeType)");
            }

            // Lire et encoder le fichier
            $fileContent = file_get_contents($cvPath);
            $base64Content = base64_encode($fileContent);

            $requestData = [
                'extractionDetails' => [
                    'name' => 'Resume Extraction',
                    'language' => 'English',
                    'fields' => [
                        [
                            'key' => 'personal_info',
                            'description' => 'Extract personal information',
                            'type' => 'object',
                            'properties' => [
                                ['key' => 'name', 'type' => 'string'],
                                ['key' => 'email', 'type' => 'string'],
                                ['key' => 'phone', 'type' => 'string'],
                                ['key' => 'address', 'type' => 'string']
                            ]
                        ],
                        [
                            'key' => 'work_experience',
                            'description' => 'Extract work history',
                            'type' => 'array',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    ['key' => 'title', 'type' => 'string'],
                                    ['key' => 'company', 'type' => 'string'],
                                    ['key' => 'start_date', 'type' => 'string'],
                                    ['key' => 'end_date', 'type' => 'string']
                                ]
                            ]
                        ]
                    ]
                ],
                'file' => $base64Content,
                'fileType' => 'pdf'
            ];

            $this->logger->debug('CV Parsing API Request', ['request' => array_merge($requestData, ['file' => 'base64_encoded_content'])]);

            $response = $this->client->request('POST', 'https://resume-parsing-api2.p.rapidapi.com/processDocument', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-rapidapi-host' => 'resume-parsing-api2.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey,
                ],
                'json' => $requestData
            ]);
    
            $result = $response->toArray();
            
            $this->logger->debug('CV Parsing API Response', ['response' => $result]);
    
            // Nouvelle vérification d'erreur plus robuste
            if (array_key_exists('error', $result)) {
                throw new \Exception($result['error']);
            }
    
            // Si l'API retourne un statut d'erreur (certaines APIs utilisent 'status' au lieu de 'error')
            if (isset($result['status']) && $result['status'] === 'error') {
                throw new \Exception($result['message'] ?? 'Erreur inconnue de l\'API');
            }
    
            // Retourner directement les données si elles existent
            if (isset($result['personal_info']) || isset($result['work_experience'])) {
                return $result;
            }
    
            // Certaines APIs encapsulent les données dans un objet 'data'
            if (isset($result['data'])) {
                return $result['data'];
            }
    
            // Si aucune structure connue n'est trouvée
            throw new \Exception('Format de réponse inattendu de l\'API');
    
        } catch (\Exception $e) {
            $this->logger->error('CV Parsing Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}