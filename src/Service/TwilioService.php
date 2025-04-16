<?php
// src/Service/TwilioService.php
namespace App\Service;

use Twilio\Rest\Client;
use Twilio\Exceptions\ConfigurationException;
use Twilio\Exceptions\TwilioException;

class TwilioService
{
    private $client;

    public function __construct(string $accountSid, string $authToken)
    {
        try {
            $this->client = new Client($accountSid, $authToken);
        } catch (ConfigurationException $e) {
            error_log('Twilio Configuration Error: ' . $e->getMessage());
            throw $e; // On propage l'exception
        }
    }

    public function sendSms(string $to, string $body): ?string
    {
        try {
            $message = $this->client->messages->create($to, [
                'from' => $_ENV['twilio_from_number'],
                'body' => $body
            ]);
            return $message->sid;
        } catch (TwilioException $e) {
            error_log('Twilio Error: ' . $e->getMessage());
            return null;
        }
    }
    // Ajoutez cette méthode
    public function getClient(): Client
    {
        return $this->client;
    }
}