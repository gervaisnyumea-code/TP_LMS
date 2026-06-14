<?php
require_once __DIR__ . '/../config/constants.php';

class EmailService {
    public static function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $apiKey = getenv('BREVO_API_KEY');
        $fromEmail = getenv('EMAIL_FROM') ?: 'gervais.nyumea@facsciences-uy1.cm';

        if (!$apiKey) {
            error_log('EmailService: BREVO_API_KEY non configurée.');
            return false;
        }

        $data = [
            'sender' => ['name' => APP_NAME, 'email' => $fromEmail],
            'to' => [['email' => $toEmail, 'name' => $toName]],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $apiKey,
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        // DEBUG LOGGING
        error_log("EmailService Response Code: $httpCode");
        error_log("EmailService Response Body: $response");
        if ($error) {
            error_log("EmailService Curl Error: $error");
        }

        return ($httpCode >= 200 && $httpCode < 300);
    }
}
