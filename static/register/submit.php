<?php
$error = false;
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

    $pythonScript = '/var/www/open/register/mail.py'; // Pfad zur Python-Datei
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

    // Skript ausführen und sowohl stdout als auch stderr erfassen
    $output = [];
    $return_var = 0;
    

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

    shell_exec("nohup $command > /dev/null 2>&1 &");


}
?>
