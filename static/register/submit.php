<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Funktion: .env-Datei laden
function loadEnvFile($filepath) {
    if (!file_exists($filepath)) {
        throw new Exception("Die Datei $filepath wurde nicht gefunden.");
    }

    $vars = [];
    $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Kommentare überspringen
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Schlüssel-Wert-Paare parsen
        list($key, $value) = explode('=', $line, 2);
        $vars[trim($key)] = trim($value);
    }
    return $vars;
}

// Lade die .env-Datei
try {
    $env = loadEnvFile('/var/private/isv/config.env');
    $smtp_server = $env['SMTP_SERVER'];
    $smtp_port = $env['SMTP_PORT'];
    $smtp_user = $env['SMTP_USER'];
    $smtp_pass = $env['SMTP_PASS'];
    $from_email = $env['FROM_EMAIL'];
    $bcc_recipients = explode(',', $env['BCC_RECIPIENTS']);
} catch (Exception $e) {
    die("<p style='color:red;'>Fehler beim Laden der Konfiguration: " . $e->getMessage() . "</p>");
}

// Überprüfung, ob POST-Request gesendet wurde
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

    // CSV-Datei-Pfad
    $dateipfad = '/var/private/isv/open25.csv';

    // Schreiben in die CSV-Datei
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

    // SMTP-E-Mail senden
    $subject = "Anmeldebestätigung";
    $body = "
    Hallo $vorname $nachname,

    Vielen Dank für Ihre Anmeldung. Hier sind Ihre übermittelten Daten:

    - Verein: $verein
    - Geburtsdatum: $geburtsdatum
    - Telefonnummer: $handy
    - FIDE-ID: $fide_id
    - Rabatt: $rabatt
    - Bestätigung: $bestaetigung
    - AGB akzeptiert: $agb
    - Teilnahme am Blitzturnier: $blitzturnier

    Mit freundlichen Grüßen,
    Ihr Team
    ";

    $headers = "From: $from_email\r\n";
    $headers .= "To: $email\r\n";
    foreach ($bcc_recipients as $bcc) {
        $headers .= "BCC: " . trim($bcc) . "\r\n";
    }
    $headers .= "Subject: $subject\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    // Verbindung zu SMTP herstellen und senden
    $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
    if (!$socket) {
        die("<p style='color:red;'>Fehler: Keine Verbindung zu SMTP-Server ($errstr).</p>");
    }

    fwrite($socket, "EHLO $smtp_server\r\n");
    fwrite($socket, "STARTTLS\r\n");
    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    fwrite($socket, "EHLO $smtp_server\r\n");
    fwrite($socket, "AUTH LOGIN\r\n");
    fwrite($socket, base64_encode($smtp_user) . "\r\n");
    fwrite($socket, base64_encode($smtp_pass) . "\r\n");
    fwrite($socket, "MAIL FROM: <$from_email>\r\n");
    fwrite($socket, "RCPT TO: <$email>\r\n");
    fwrite($socket, "DATA\r\n");
    fwrite($socket, "$headers\r\n$body\r\n.\r\n");
    fwrite($socket, "QUIT\r\n");

    $response = stream_get_contents($socket);
    fclose($socket);

    if (strpos($response, "250") !== false) {
        echo "<p style='color:green;'>Die E-Mail wurde erfolgreich gesendet.</p>";
    } else {
        echo "<p style='color:red;'>Fehler beim Senden der E-Mail: $response</p>";
    }
}
?>
