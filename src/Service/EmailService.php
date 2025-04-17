<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger
    ) {}

    public function sendStatusUpdateEmail(string $recipient, string $status): bool
    {
        try {
            $logoPath = 'C:/Users/Dell/Desktop/HR360_web/public/images/logoRH360.png';
            $logoCid = 'logoRH360';

            $email = (new Email())
                ->from('no-reply@votre-domaine.com')
                ->to($recipient)
                ->subject("Mise à jour de candidature - $status")
                ->html($this->generateEmailContent($status, $logoCid))
                ->embedFromPath($logoPath, $logoCid); // intègre le logo dans le corps de l’email

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Erreur envoi email: ' . $e->getMessage());
            return false;
        }
    }

    private function generateEmailContent(string $status, string $logoCid): string
    {
        $content = <<<HTML
        <html>
            <body style="font-family: Arial, sans-serif;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="cid:$logoCid" alt="Logo RH360" style="max-height: 100px;" />
                </div>
                <h2 style="color: #2c3e50;">Résultat de votre candidature</h2>
                <p>Bonjour,</p>
                <p>Le statut de votre candidature a été mis à jour : <strong>$status</strong></p>
        HTML;

        if ($status === "Accepté") {
            $content .= '<p style="color: #27ae60;">Félicitations ! Nous vous contacterons bientôt pour les prochaines étapes.</p>';
        } else {
            $content .= '<p>Nous vous remercions pour l\'intérêt porté à notre entreprise.</p>';
        }

        $content .= <<<HTML
                <p>Cordialement,<br>L'équipe RH</p>
            </body>
        </html>
        HTML;

        return $content;
    }
}