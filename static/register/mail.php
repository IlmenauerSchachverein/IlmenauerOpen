<?php
$filePath = '/var/private/pw.txt';

// Überprüfen, ob die Datei existiert
if (file_exists($filePath)) {
    // Inhalt der Datei lesen und in einer Variable speichern
    $password = file_get_contents($filePath);

    // Inhalt ausgeben oder weiterverwenden
    echo "Das Passwort lautet: " . $password;
} else {
    echo "Die Datei existiert nicht: " . $filePath;
}


// PHPMailer einbinden
require '/var/www/open/register/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Erstelle eine neue Instanz von PHPMailer
$mail = new PHPMailer(true);

try {
    // Server-Einstellungen
    $mail->isSMTP();                                          // SMTP verwenden
    $mail->Host       = 'smtp.mailbox.org';                   // SMTP-Server-Adresse
    $mail->SMTPAuth   = true;                                 // SMTP-Authentifizierung aktivieren
    $mail->Username   = 'erik.skopp@mailbox.org';      // SMTP-Benutzername
    $mail->Password   = $password;                      // SMTP-Passwort
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Verschlüsselung (STARTTLS)
    $mail->Port       = 465;                                 // TCP-Port für SMTP (z. B. 587 für TLS)

    // Absender und Empfänger
    $mail->setFrom('erik.skopp@mailbox.org', 'Dein Name');   // Absender-Adresse und Name
    $mail->addAddress('skopp.erik@gmail.com', 'Empfaenger'); // Empfänger-Adresse und Name

    // E-Mail-Inhalt
    $mail->isHTML(true);                                      // E-Mail-Inhalt als HTML
    $mail->Subject = 'Test-E-Mail mit PHPMailer';             // Betreff der E-Mail
    $mail->Body    = '<h1>Hallo!</h1><p>Dies ist eine Test-E-Mail.</p>'; // HTML-Inhalt
    $mail->AltBody = 'Hallo! Dies ist eine Test-E-Mail.';     // Nur-Text-Version

    // E-Mail senden
    $mail->send();
    echo 'E-Mail wurde erfolgreich gesendet.';
} catch (Exception $e) {
    echo "E-Mail konnte nicht gesendet werden. Fehler: {$mail->ErrorInfo}";
}
?>
