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
        $this->logger->info("Tentative d'envoi d'email à {$recipient} avec statut {$status}");
        
        try {
            $logoPath = 'C:/Users/user/OneDrive - ESPRIT/Bureau/ProjetPidev/HR360_web/public/images/logoRH360.png';
            $this->logger->debug("Chemin du logo: {$logoPath}");
            
            if (!file_exists($logoPath)) {
                $this->logger->error("Fichier logo introuvable: {$logoPath}");
                throw new \RuntimeException("Logo file not found");
            }
    
            $logoCid = 'logoRH360';
            $this->logger->debug("Préparation de l'email...");
            
            $email = (new Email())
                ->from('kousaynajar147@gmail.com')
                ->to($recipient)
                ->subject("Mise à jour de candidature - $status")
                ->html($this->generateEmailContent($status, $logoCid))
                ->embedFromPath($logoPath, $logoCid);
    
            $this->logger->debug("Email construit, envoi en cours...");
            $this->mailer->send($email);
            
            $this->logger->info("Email envoyé avec succès à {$recipient}");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Échec de l'envoi d'email: " . $e->getMessage());
            $this->logger->error("Stack trace: " . $e->getTraceAsString());
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