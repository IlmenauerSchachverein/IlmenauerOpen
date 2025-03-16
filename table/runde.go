package main

import (
	"bufio"
	"fmt"
	"os"
	"path/filepath"
	"strings"
)

// convertRundeToMarkdown wandelt einen <runde> Block in eine Markdown-Tabelle um.
// Es werden 14 Spalten erwartet, wobei:
// - Linke Seite: Spalten 0 bis 5 (Tisch, TNr, Teilnehmer, Titel, Punkte, -)
// - Rechte Seite: Spalten 6 bis 9 (TNr, Teilnehmer, Titel, Punkte)
// - Die Spalten 10, 11 und 12 werden zu einer Spalte "Ergebnis" zusammengeführt.
// - Spalte 13 wird verworfen.
func convertRundeToMarkdown(runde string) string {
	lines := strings.Split(runde, "\n")
	var cleanLines []string
	for _, line := range lines {
		line = strings.TrimSpace(line)
		if line != "" {
			cleanLines = append(cleanLines, line)
		}
	}

	// Fester Header
	headers := []string{"Tisch", "TNr", "Teilnehmer", "Titel", "Punkte", "-", "TNr", "Teilnehmer", "Titel", "Punkte", "Ergebnis"}
	mdLines := []string{
		"| " + strings.Join(headers, " | ") + " |",
		"| " + strings.Repeat("----- | ", len(headers)),
	}

	for _, line := range cleanLines {
		// Aufspalten der Zeile anhand von Tabulatoren
		columns := strings.Split(line, "\t")
		// Falls weniger als 14 Spalten vorhanden, mit leeren Strings auffuellen
		for len(columns) < 14 {
			columns = append(columns, "")
		}
		if len(columns) < 14 {
			fmt.Println("Warning: Ungueltige Zeile uebersprungen (zu wenige Spalten):", line)
			continue
		}

		// Zusammenfuehren der Spalten 10, 11 und 12 zu einer Spalte "Ergebnis"
		merged := strings.TrimSpace(columns[10]) + " " + strings.TrimSpace(columns[11]) + " " + strings.TrimSpace(columns[12])
		merged = strings.Join(strings.Fields(merged), " ") // entfernt ueberfluessige Leerzeichen

		newColumns := []string{
			strings.TrimSpace(columns[0]), // Tisch
			strings.TrimSpace(columns[1]), // TNr
			strings.TrimSpace(columns[2]), // Teilnehmer
			strings.TrimSpace(columns[3]), // Titel
			strings.TrimSpace(columns[4]), // Punkte
			strings.TrimSpace(columns[5]), // -
			strings.TrimSpace(columns[6]), // TNr (rechte Seite)
			strings.TrimSpace(columns[7]), // Teilnehmer (rechte Seite)
			strings.TrimSpace(columns[8]), // Titel (rechte Seite)
			strings.TrimSpace(columns[9]), // Punkte (rechte Seite)
			merged,                        // Ergebnis (gemergt)
		}

		mdLines = append(mdLines, "| "+strings.Join(newColumns, " | ")+" |")
	}

	return strings.Join(mdLines, "\n")
}

// processFiles durchsucht rekursiv alle .md Dateien im angegebenen Verzeichnis.
func processFiles(root string) {
	err := filepath.WalkDir(root, func(path string, d os.DirEntry, err error) error {
		if err != nil {
			fmt.Println("Fehler beim Zugriff auf Datei:", err)
			return err
		}
		if !d.IsDir() && filepath.Ext(path) == ".md" {
			processFile(path)
		}
		return nil
	})
	if err != nil {
		fmt.Println("Fehler beim Verarbeiten der Dateien:", err)
	}
}

// processFile bearbeitet eine einzelne Markdown-Datei und ersetzt <runde> Blöcke.
func processFile(path string) {
	file, err := os.Open(path)
	if err != nil {
		fmt.Println("Fehler beim Oeffnen der Datei:", err)
		return
	}
	defer file.Close()

	var content []string
	scanner := bufio.NewScanner(file)
	var insideRunde bool
	var blockRunde []string

	for scanner.Scan() {
		line := scanner.Text()
		if strings.Contains(line, "<runde>") {
			insideRunde = true
			blockRunde = []string{}
			continue
		}
		if strings.Contains(line, "</runde>") {
			insideRunde = false
			mdTable := convertRundeToMarkdown(strings.Join(blockRunde, "\n"))
			content = append(content, mdTable)
			continue
		}
		if insideRunde {
			blockRunde = append(blockRunde, line)
			continue
		}
		content = append(content, line)
	}
	if err := scanner.Err(); err != nil {
		fmt.Println("Fehler beim Lesen der Datei:", err)
		return
	}
	originalContent, _ := os.ReadFile(path)
	newContent := strings.Join(content, "\n")
	if string(originalContent) != newContent {
		err = os.WriteFile(path, []byte(newContent), 0644)
		if err != nil {
			fmt.Println("Fehler beim Schreiben der Datei:", err)
		} else {
			fmt.Println("Datei erfolgreich aktualisiert:", path)
		}
	}
}

func main() {
	contentDir := "./content"
	processFiles(contentDir)
}
