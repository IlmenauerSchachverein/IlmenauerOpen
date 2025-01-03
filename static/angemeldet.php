<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmeldungen Open 2025</title>
    <style>
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #4CAF50;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e9e9e9;
        }
        .highlight {
            background-color: #ffcccc; /* Hellrot für Übereinstimmungen */
        }
    </style>
</head>
<body>
    <h1>Anmeldungen Open 2025</h1>

    <?php
    // Pfad zur CSV-Datei
    $csvFile = '/var/private/isv/open25.csv';

    // Überprüfen, ob die CSV-Datei existiert
    if (!file_exists($csvFile)) {
        echo '<p style="color: red; text-align: center;">Fehler: Die CSV-Datei existiert nicht.</p>';
        exit;
    }

    // Chess-Results-Daten abrufen
    $chessResultsUrl = 'https://chess-results.com/tnr1056111.aspx?lan=0';
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
            if ($cells->length >= 4) {
                $webNames[] = trim($cells->item(3)->nodeValue); // Spielername aus der vierten Zelle
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
        <th>AGB</th>
        <th>Blitzturnier</th>
        <th>ChessResults</th>
    </tr>';

    // Datei öffnen und Zeilen auslesen
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        $headerSkipped = false;
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            // Überprüfen, ob die Zeile genügend Spalten enthält
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
            $agb = htmlspecialchars($data[10]);
            $blitzturnier = htmlspecialchars($data[11]);

            // Zeile in die Tabelle ausgeben
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
                <td>{$agb}</td>
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
</body>
</html>
