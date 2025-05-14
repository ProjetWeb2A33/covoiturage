<?php

require  'C:\xampp\htdocs\integration\vendor-emna\phpmailer\src\PHPMailer.php';
require 'C:\xampp\htdocs\integration\vendor-emna\phpmailer\src\SMTP.php';
require 'C:\xampp\htdocs\integration\vendor-emna\phpmailer\src\Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Mail Controller
 * 
 * Handles all email functionality for the application
 */
class MailC
{
    // SMTP server credentials
    private $host = 'smtp.gmail.com';
    private $port = '587';            // Common ports: 587 (TLS), 465 (SSL)
    private $username = 'bhemna05@gmail.com'; // Your full email address
    private $password = 'djls pequ djih ixsh'; // For Gmail, use an App Password
    private $encryption = 'tls';      // 'tls' or 'ssl'
    private $fromEmail = 'bhemna05@gmail.com'; // Should match username for most providers
    private $fromName = 'EasyParki';

    /**
     * Send an email using PHPMailer with a real SMTP server
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $altBody Plain text alternative (optional)
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendEmail($to, $subject, $body, $altBody = '')
    {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Disable debugging in production environment
            $mail->SMTPDebug = 0; // 0 = off, 1 = client messages, 2 = client and server messages

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = $this->encryption;
            $mail->Port = $this->port;
            $mail->CharSet = 'UTF-8';

            // Recipients
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->AltBody = !empty($altBody) ? $altBody : strip_tags($body);

            // Send the email
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log the error with detailed information
            $errorMessage = "Email sending failed: {$mail->ErrorInfo}\n";
            $errorMessage .= "To: $to\n";
            $errorMessage .= "Subject: $subject\n";
            $errorMessage .= "Error details: {$e->getMessage()}\n";
            $errorMessage .= "File: {$e->getFile()}, Line: {$e->getLine()}\n";

            error_log($errorMessage);

            // Write to a custom log file for better debugging
            file_put_contents(__DIR__ . '/../logs/email_errors.log', date('[Y-m-d H:i:s] ') . $errorMessage . "\n", FILE_APPEND);

            return false;
        }
    }

    /**
     * Send a vacation plan confirmation email
     *
     * @param array $planData Vacation plan data
     * @param string $hotelName Hotel name
     * @param bool $isUpdate Whether this is an update or a new plan
     * @return bool True if email was sent successfully, false otherwise
     */
    public function sendVacationPlanConfirmation($planData, $hotelName, $isUpdate = false)
    {
        $action = $isUpdate ? 'mis à jour' : 'créé';
        $subject = "Votre plan de vacances a été $action avec succès";

        // Create HTML email body
        $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #0a1d37; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
                h1 { color: #0a1d37; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details th, .details td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                .details th { background-color: #f2f2f2; }
                .button { display: inline-block; background-color: #0a1d37; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>EasyParki</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour {$planData['nom_utilisateur']},</h2>
                    <p>Votre plan de vacances a été $action avec succès.</p>
                    
                    <div class='details'>
                        <h3>Détails de votre plan:</h3>
                        <table>
                            <tr>
                                <th>Identifiant</th>
                                <td>{$planData['identifiant']}</td>
                            </tr>
                            <tr>
                                <th>Date de départ</th>
                                <td>{$planData['date_depart']}</td>
                            </tr>
                            <tr>
                                <th>Date de retour</th>
                                <td>{$planData['date_retour']}</td>
                            </tr>
                            <tr>
                                <th>Type de transport</th>
                                <td>{$planData['type_transport']}</td>
                            </tr>
                            <tr>
                                <th>Hôtel</th>
                                <td>$hotelName</td>
                            </tr>
                            <tr>
                                <th>Location de voiture</th>
                                <td>{$planData['location_voiture']}</td>
                            </tr>
                            <tr>
                                <th>Besoin de parking</th>
                                <td>{$planData['besoin_parking']}</td>
                            </tr>
                        </table>
                    </div>

                    <p>Vous pouvez consulter et modifier votre plan à tout moment en vous connectant à votre compte EasyParki.</p>

                    <p>Nous vous souhaitons d'excellentes vacances!</p>

                    <p>Cordialement,<br>L'équipe EasyParki</p>
                </div>
                <div class='footer'>
                    <p>Ce message a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                    <p>&copy; " . date('Y') . " EasyParki. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Plain text alternative
        $altBody = "Bonjour {$planData['nom_utilisateur']},\n\n" .
                  "Votre plan de vacances a été $action avec succès.\n\n" .
                  "Détails de votre plan:\n" .
                  "- Identifiant: {$planData['identifiant']}\n" .
                  "- Date de départ: {$planData['date_depart']}\n" .
                  "- Date de retour: {$planData['date_retour']}\n" .
                  "- Type de transport: {$planData['type_transport']}\n" .
                  "- Hôtel: $hotelName\n" .
                  "- Location de voiture: {$planData['location_voiture']}\n" .
                  "- Besoin de parking: {$planData['besoin_parking']}\n\n" .
                  "Vous pouvez consulter et modifier votre plan à tout moment en vous connectant à votre compte EasyParki.\n\n" .
                  "Nous vous souhaitons d'excellentes vacances!\n\n" .
                  "Cordialement,\n" .
                  "L'équipe EasyParki";

        // Send the email
        return $this->sendEmail($planData['email'], $subject, $body, $altBody);
    }
}
