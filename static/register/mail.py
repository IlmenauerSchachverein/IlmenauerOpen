import os
import sys
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

# Funktion: .env-Datei laden
def load_env_file(filepath):
    """Lädt eine .env-Datei und setzt die Variablen in os.environ."""
    if not os.path.exists(filepath):
        raise FileNotFoundError(f"Die Datei {filepath} wurde nicht gefunden.")
    with open(filepath, 'r') as f:
        for line in f:
            # Leere Zeilen und Kommentare überspringen
            line = line.strip()
            if not line or line.startswith('#'):
                continue
            # Schlüssel-Wert-Paare parsen
            key, value = line.split('=', 1)
            os.environ[key.strip()] = value.strip()

# .env-Datei laden
env_path = "/var/private/isv/open.env"  # Pfad zur .env-Datei
load_env_file(env_path)

# SMTP-Konfiguration aus Umgebungsvariablen laden
SMTP_SERVER = os.getenv("SMTP_SERVER")
SMTP_PORT = int(os.getenv("SMTP_PORT"))
SMTP_USER = os.getenv("SMTP_USER")
SMTP_PASS = os.getenv("SMTP_PASS")

# Debugging: Zeige die geladenen Umgebungsvariablen an
print(f"""
Geladene Umgebungsvariablen:
- SMTP_SERVER: {SMTP_SERVER}
- SMTP_PORT: {SMTP_PORT}
- SMTP_USER: {SMTP_USER}
- SMTP_PASS: {SMTP_PASS[:2]}*** (aus Sicherheitsgründen gekürzt)
""")

# Überprüfe, ob genügend Argumente übergeben wurden
if len(sys.argv) < 8:
    print("Fehler: Zu wenige Argumente.")
    print("Verwendung: python mail.py <Empfänger> <Vorname> <Nachname> <Verein> <Geburtsdatum> <Telefonnummer> <FIDE-ID>")
    sys.exit(1)

# Argumente zuweisen
to_email = sys.argv[1]
vorname = sys.argv[2]
nachname = sys.argv[3]
verein = sys.argv[4]
geburtsdatum = sys.argv[5]
telefonnummer = sys.argv[6]
fide_id = sys.argv[7]

# Optionale Felder verarbeiten (falls mehr als 8 Argumente)
rabatt = sys.argv[8] if len(sys.argv) > 8 else "Nicht angegeben"
bestaetigung = sys.argv[9] if len(sys.argv) > 9 else "Nein"
agb = sys.argv[10] if len(sys.argv) > 10 else "?"
blitzturnier = sys.argv[11] if len(sys.argv) > 11 else "Nein"

# Debugging: Zeige die verarbeiteten Daten an
print(f"""
Erhaltene Daten:
- Empfänger: {to_email}
- Vorname: {vorname}
- Nachname: {nachname}
- Verein: {verein}
- Geburtsdatum: {geburtsdatum}
- Telefonnummer: {telefonnummer}
- FIDE-ID: {fide_id}
- Rabatt: {rabatt}
- Bestätigung: {bestaetigung}
- AGB: {agb}
- Blitzturnier: {blitzturnier}
""")

# E-Mail-Inhalt
message = f"""
Hallo {vorname} {nachname},

vielen Dank für Ihre Anmeldung. Hier sind Ihre übermittelten Daten:

- Verein: {verein}
- Geburtsdatum: {geburtsdatum}
- Telefonnummer: {telefonnummer}
- FIDE-ID: {fide_id}
- Rabatt: {rabatt}
- Bestätigung: {bestaetigung}
- AGB akzeptiert: {agb}
- Teilnahme am Blitzturnier: {blitzturnier}

Mit freundlichen Grüßen,
Ihr Team
"""

# E-Mail erstellen
msg = MIMEMultipart()
msg['From'] = SMTP_USER
msg['To'] = to_email
msg['Subject'] = "Anmeldebestätigung"
msg.attach(MIMEText(message, 'plain'))

# Verbindung zum SMTP-Server herstellen und E-Mail senden
try:
    with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
        server.starttls()  # TLS aktivieren
        server.login(SMTP_USER, SMTP_PASS)
        server.sendmail(SMTP_USER, to_email, msg.as_string())
    print("E-Mail erfolgreich gesendet!")
except Exception as e:
    print(f"Fehler beim Senden der E-Mail: {e}")
    sys.exit(1)
