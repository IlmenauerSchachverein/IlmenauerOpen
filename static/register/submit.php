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
    $blitzturnier = isset($_POST['blitzturnier']) ? 'Ja' : 'Nein'; // Neues Feld

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
            $blitzturnier // Blitzturnier hinzufügen
        ];

        if (($datei = fopen($dateipfad, 'a')) !== FALSE) {
            fputcsv($datei, $datenzeile);
            fclose($datei);
            echo "<p style='color:green;'>Erfolg: Ihre Daten wurden gespeichert.</p>";
        } else {
            echo "<p style='color:red;'>Fehler: CSV-Datei konnte nicht geöffnet werden.</p>";
        }
    }
}
?>
