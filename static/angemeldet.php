<?php
// Pfad zur CSV-Datei
$csvFile = '/var/private/isv/open25.csv';

// Überprüfen, ob die CSV-Datei existiert
if (!file_exists($csvFile)) {
    echo '<p style="color: red; text-align: center;">Fehler: Die CSV-Datei existiert nicht.</p>';
    exit;
}

// Chess-Results-Daten abrufen
$chessResultsUrl = 'https://chess-results.com/tnr1056124.aspx?lan=0';
$html = file_get_contents($chessResultsUrl);

if ($html === FALSE) {
    echo '<p style="color: red; text-align: center;">Fehler: Die Chess-Results-Seite konnte nicht geladen werden.</p>';
    $webNames = [];
} else {
    // Chess-Results-Daten parsen
    $dom = new DOMDocument;
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Tabelle parsen: Suche alle Spieler in der entsprechenden Tabelle
    $webNames = [];
    $rows = $xpath->query("//table[contains(@class, 'CRs')]//tr");
    foreach ($rows as $index => $row) {
        if ($index === 0) continue; // Header-Zeile überspringen
        $cells = $row->getElementsByTagName('td');
        if ($cells->length >= 3) {
            $webNames[] = trim($cells->item(2)->nodeValue); // Spielername aus der dritten Zelle
        }
    }
}

// Tabelle anzeigen
echo '<table>';
echo '<tr>
    <th>Datum</th>
    <th>Zeit</th>
    <th>Vorname</th>
    <th>Nachname</th>
    <th>Verein</th>
    <th>Geburtsdatum</th>
    <th>Handynummer</th>
    <th>E-Mail</th>
    <th>Rabattberechtigung</th>
    <th>Bestätigung</th>
    <th>Blitzturnier</th>
    <th>ChessResults</th>
</tr>';

// Datei öffnen und Zeilen auslesen
if (($handle = fopen($csvFile, 'r')) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        if (count($data) < 12) {
            echo '<p style="color: red;">Fehler: Eine Zeile in der CSV-Datei hat nicht genügend Spalten.</p>';
            continue;
        }

        // Vollständigen Namen erstellen
        $fullName = trim($data[3]) . ', ' . trim($data[2]); // Nachname, Vorname

        // Chess-Results-Abgleich
        $chessResultsMatch = in_array($fullName, $webNames) ? 'X' : '';

        // Daten aus der CSV extrahieren
        $datum = htmlspecialchars($data[0]);
        $zeit = htmlspecialchars($data[1]);
        $vorname = htmlspecialchars($data[2]);
        $nachname = htmlspecialchars($data[3]);
        $verein = htmlspecialchars($data[4]);
        $geburtsdatum = htmlspecialchars($data[5]);
        $handynummer = htmlspecialchars($data[6]);
        $email = htmlspecialchars($data[7]);
        $rabatt = htmlspecialchars($data[8]);
        $bestaetigung = htmlspecialchars($data[9]);
        $blitzturnier = htmlspecialchars($data[11]);

        // Zeile in die Tabelle ausgeben (ohne AGB-Spalte)
        echo "<tr class='" . ($chessResultsMatch ? 'highlight' : '') . "'>
            <td>{$datum}</td>
            <td>{$zeit}</td>
            <td>{$vorname}</td>
            <td>{$nachname}</td>
            <td>{$verein}</td>
            <td>{$geburtsdatum}</td>
            <td>{$handynummer}</td>
            <td>{$email}</td>
            <td>{$rabatt}</td>
            <td>{$bestaetigung}</td>
            <td>{$blitzturnier}</td>
            <td>{$chessResultsMatch}</td>
        </tr>";
    }
    fclose($handle);
}

echo '</table>';

// Liste der Spieler auf ChessResults, die nicht in der CSV sind
$csvNames = array_map(function ($data) {
    return trim($data[3]) . ', ' . trim($data[2]);
}, array_filter(array_map('str_getcsv', file($csvFile)), fn($line) => count($line) >= 12));

$notInCsv = array_diff($webNames, $csvNames);
echo '<h2>Auf ChessResults gemeldet, aber nicht in der CSV-Datei:</h2>';
if (!empty($notInCsv)) {
    echo '<ul>';
    foreach ($notInCsv as $name) {
        echo "<li>{$name}</li>";
    }
    echo '</ul>';
} else {
    echo '<p>Alle gemeldeten Spieler sind auch in der CSV-Datei vorhanden.</p>';
}
?>
