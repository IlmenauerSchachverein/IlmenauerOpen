<?php
$error = false;

// Überprüfung, ob POST-Request gesendet wurde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingaben validieren und absichern
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

    // Honeypot-Schutz
    if (!empty($_POST['honeypot'])) {
        die("<p style='color:red;'>Fehler: Spam erkannt.</p>");
    }

    // Dateipfad zur CSV-Datei
    $dateipfad = '/var/private/isv/open25.csv';

    // Geburtsdatum validieren
    if (!preg_match("/^\d{2}\.\d{2}\.\d{4}$/", $geburtsdatum) || !checkdate((int)explode('.', $geburtsdatum)[1], (int)explode('.', $geburtsdatum)[0], (int)explode('.', $geburtsdatum)[2])) {
        echo "<p style='color:red;'>Bitte geben Sie ein gültiges Geburtsdatum ein.</p>";
        $error = true;
    }

    if (!$error) {
        // Daten speichern
        $datenzeile = [
            date('d-m-Y'), // Datum
            date('H:i:s'), // Zeit
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

        if (($datei = fopen($dateipfad, 'a')) !== FALSE) {
            fputcsv($datei, $datenzeile);
            fclose($datei);
            echo "<p style='color:green;'>Erfolg: Ihre Daten wurden gespeichert.</p>";

            // Python-Skript ausführen
            $pythonScript = '/var/www/open/register/mail.py'; // Pfad zum Python-Skript
            $pythonPath = '/usr/bin/python3'; // Absoluter Pfad zu Python 3

            // Befehl vorbereiten
            $command = escapeshellcmd("$pythonPath $pythonScript " .
                escapeshellarg($email) . " " .
                escapeshellarg($vorname) . " " .
                escapeshellarg($nachname) . " " .
                escapeshellarg($verein) . " " .
                escapeshellarg($geburtsdatum) . " " .
                escapeshellarg($handy) . " " .
                escapeshellarg($rabatt) . " " .
                escapeshellarg($bestaetigung) . " " .
                escapeshellarg($agb) . " " .
                escapeshellarg($blitzturnier) . " " .
                escapeshellarg($fide_id));

            // Skript ausführen und Ergebnis prüfen
            $output = [];
            $return_var = 0;
            exec($command, $output, $return_var);

            // Debugging-Ausgabe
            echo "<h3>Debugging-Informationen:</h3>";
            echo "<p><strong>Ausgeführter Befehl:</strong> <code>$command</code></p>";

            if ($return_var === 0) {
                echo "<p style='color:green;'>Die E-Mail wurde erfolgreich gesendet.</p>";
            } else {
                echo "<p style='color:red;'>Fehler beim Senden der E-Mail.</p>";
                echo "<p><strong>Rückgabewert:</strong> $return_var</p>";
                echo "<p><strong>Ausgabe:</strong></p>";
                echo "<pre>" . implode("\n", $output) . "</pre>";
            }
        } else {
            echo "<p style='color:red;'>Fehler: CSV-Datei konnte nicht geöffnet werden.</p>";
        }
    }
}
?>
