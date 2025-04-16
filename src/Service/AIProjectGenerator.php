<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIProjectGenerator
{
    private $httpClient;
    private $apiKey = 'e499094f92e24b0998442f639ba731339642e363798e4b2f96459c8c40725dc7';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function generateProject(string $name, \DateTimeInterface $startDate, \DateTimeInterface $endDate, int $taskCount): array
    {
        $prompt = $this->createPrompt($name, $startDate, $endDate, $taskCount);
        
        try {
            $response = $this->httpClient->request('POST', 'https://api.together.xyz/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'meta-llama/Llama-3.3-70B-Instruct-Turbo',
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]
            ]);

            $content = $response->toArray();
            return $this->parseResponse($content);
            
        } catch (\Exception $e) {
            throw new \RuntimeException('AI generation failed: ' . $e->getMessage());
        }
    }

    private function createPrompt(string $name, \DateTimeInterface $startDate, \DateTimeInterface $endDate, int $taskCount): string
    {
        return sprintf(
            "Generate a structured JSON project with %d tasks for a project named %s with start date: %s and end date: %s\n" .
            "The JSON must follow this exact format without any extra text: " .
            "{ \"projectName\": \"Project Name\", " .
            "\"description\": \"Project Description\", " .
            "\"startDate\": \"YYYY-MM-DD\", " .
            "\"endDate\": \"YYYY-MM-DD\", " .
            "\"tasks\": [ { \"name\": \"Task Name\", \"description\": \"Task Description\", " .
            "\"startDate\": \"YYYY-MM-DD\", \"endDate\": \"YYYY-MM-DD\" } ] }" .
            " Ensure all task dates are within the project start and end dates. Allow dates overlapping." .
            " The task names should be meaningful and reflect the purpose of the task. " .
            " The task descriptions should be detailed and explain what the task involves.",
            $taskCount,
            $name,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );
    }

    private function parseResponse(array $response): array
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid AI response format');
        }

        $content = $response['choices'][0]['message']['content'];
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to parse AI response: ' . json_last_error_msg());
        }

        return $data;
    }
}