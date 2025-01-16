<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '/var/www/open/register/vendor/autoload.php'; // Autoloader von Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Funktion zum Laden der `.env`-Datei
function loadEnvFile($filepath) {
    if (!file_exists($filepath)) {
        throw new Exception("Die Datei $filepath wurde nicht gefunden.");
    }

    $vars = [];
    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Kommentare überspringen
        list($key, $value) = explode('=', $line, 2);
        $vars[trim($key)] = trim($value);
    }
    return $vars;
}

try {
    $env = loadEnvFile('/var/private/isv/open.env');
    $smtp_server = $env['SMTP_SERVER'];
    $smtp_port = $env['SMTP_PORT'];
    $smtp_user = $env['SMTP_USER'];
    $smtp_pass = $env['SMTP_PASS'];
    $from_email = $env['FROM_EMAIL'];
    $bcc_recipients = explode(',', $env['BCC_RECIPIENTS']);
} catch (Exception $e) {
    die("<p style='color:red;'>Fehler beim Laden der Konfiguration: " . $e->getMessage() . "</p>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vorname = htmlspecialchars($_POST['vorname']);
    $nachname = htmlspecialchars($_POST['nachname']);
    $verein = isset($_POST['verein']) ? htmlspecialchars($_POST['verein']) : '';
    $geburtsdatum = htmlspecialchars($_POST['geburtsdatum']);
    $handy = isset($_POST['handy']) ? htmlspecialchars($_POST['handy']) : 'Nicht angegeben';
    $email = htmlspecialchars($_POST['email']);
    $rabatt = htmlspecialchars($_POST['rabatt']);
    $bestaetigung = 'Nein';
    $agb = '?';
    $blitzturnier = isset($_POST['blitzturnier']) ? 'Ja' : 'Nein';
    $fide_id = isset($_POST['fide_id']) ? htmlspecialchars($_POST['fide_id']) : '';

    // CSV-Datei speichern
    $dateipfad = '/var/private/isv/open25.csv';
    if (($datei = fopen($dateipfad, 'a')) !== FALSE) {
        $datenzeile = [
            date('d-m-Y'),
            date('H:i:s'),
            $vorname,
            $nachname,
            $verein,
            $geburtsdatum,
            $handy,
            $email,
            $rabatt,
            $bestaetigung,
            $agb,
            $blitzturnier,
            $fide_id
        ];
        fputcsv($datei, $datenzeile);
        fclose($datei);
        echo "<p style='color:green;'>Erfolg: Ihre Daten wurden gespeichert.</p>";
    } else {
        die("<p style='color:red;'>Fehler: CSV-Datei konnte nicht geöffnet werden.</p>");
    }

    // E-Mail senden mit PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Server-Einstellungen
        $mail->isSMTP();
        $mail->Host = $smtp_server;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_user;
        $mail->Password = $smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtp_port;

        // Absender und Empfänger
        $mail->setFrom($from_email, 'Ilmenauer Schachverein');
        $mail->addAddress($email, "$vorname $nachname");

        // BCC-Empfänger hinzufügen
        foreach ($bcc_recipients as $bcc) {
            $mail->addBCC(trim($bcc));
        }

        // E-Mail-Inhalt
        $mail->isHTML(true);
        $mail->Subject = "Anmeldebestätigung";
        $mail->Body = "
        <p>Hallo $vorname $nachname,</p>
        <p>Vielen Dank für Ihre Anmeldung. Hier sind Ihre übermittelten Daten:</p>
        <ul>
            <li>Verein: $verein</li>
            <li>Geburtsdatum: $geburtsdatum</li>
            <li>Telefonnummer: $handy</li>
            <li>FIDE-ID: $fide_id</li>
            <li>Rabatt: $rabatt</li>
            <li>Bestätigung: $bestaetigung</li>
            <li>AGB akzeptiert: $agb</li>
            <li>Teilnahme am Blitzturnier: $blitzturnier</li>
        </ul>
        <p>Mit freundlichen Grüßen,<br>Ihr Team</p>";
        $mail->AltBody = "Hallo $vorname $nachname,\n\nVielen Dank für Ihre Anmeldung. Hier sind Ihre übermittelten Daten:\n
        Verein: $verein
        Geburtsdatum: $geburtsdatum
        Telefonnummer: $handy
        FIDE-ID: $fide_id
        Rabatt: $rabatt
        Bestätigung: $bestaetigung
        AGB akzeptiert: $agb
        Teilnahme am Blitzturnier: $blitzturnier\n\nMit freundlichen Grüßen,\nIhr Team";

        $mail->send();
        echo "<p style='color:green;'>Die E-Mail wurde erfolgreich gesendet.</p>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Fehler beim Senden der E-Mail: {$mail->ErrorInfo}</p>";
    }
}
?>
