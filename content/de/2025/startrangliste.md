---
title: Startrangliste
toc: false
type: docs
---

{{< callout type="info" >}}
Dieses Jahr wird die DWZ (Deutsche Wertungszahl) als Turnierwertungszahl (TWZ) verwendet, wobei die Elo-Zahl unberücksichtigt bleibt.
{{< /callout >}}

{{< callout type="warning" >}}
Das Orgateam macht das Turnier ehrenamtlich. Daher kann es durchaus 3 bis 4 Tage dauern bis ihr Name in der Liste erscheint. Haben sie bitte etwas Gedult. Sollte ihr Name nach einer Woche noch nicht erschienen sein, können sie uns gerne unter [info@ilmenauer-schachverein.de](mailto:info@ilmenauer-schachverein.de) eine E-Mail schreiben. Eine individuelle Antwort per E-Mail als Anmeldebestätigung gibt es aus zeitgründen nicht.
{{< /callout >}}

- Stand: 25.03.2025 22:15

- {{< commit-info >}}

<noscript>
{{< callout type="error" >}}
Ihr Browser unterstüzt kein Java Script. Bitte aktivieren Sie Java Script.
{{< /callout >}}

## Startrangliste (ohne JS)
Die Tabelle ist auch Live und aktuell. Für das Design und Rendering braucht es JS.
<iframe src="https://register.ilmenauer-schachverein.de/open/startrangliste.php" width="100%" height="1200px" style="border: none;"></iframe>

</noscript>

<div id="startrangliste">
</div>
<script>
fetch("https://register.ilmenauer-schachverein.de/open/startrangliste.php")
  .then(response => response.text())
  .then(html => {
    document.getElementById("startrangliste").innerHTML = html;
  })
  .catch(error => {
    document.getElementById("startrangliste").innerText = "Teilnehmerliste konnte nicht geladen werden.";
    console.error(error);
  });
</script>
