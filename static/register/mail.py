import os
from dotenv import load_dotenv
import sys
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

# .env-Datei laden (korrekter Pfad)
load_dotenv("/var/private/isv/open.env")

# Aus Umgebungsvariablen lesen
SMTP_SERVER = os.getenv("SMTP_SERVER")
SMTP_PORT = os.getenv("SMTP_PORT")
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

# Debugging: Zeige die übergebenen Argumente an
print(f"Erhaltene Argumente: {sys.argv}")

# Überprüfe, ob genügend Argumente übergeben wurden
if len(sys.argv) < 8:  # Mindestanzahl der erwarteten Argumente
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
Empfänger: {to_email}
Vorname: {vorname}
Nachname: {nachname}
Verein: {verein}
Geburtsdatum: {geburtsdatum}
Telefonnummer: {telefonnummer}
FIDE-ID: {fide_id}
Rabatt: {rabatt}
Bestätigung: {bestaetigung}
AGB: {agb}
Blitzturnier: {blitzturnier}
""")

# SMTP-Konfiguration und E-Mail-Versand
try:
    with smtplib.SMTP(SMTP_SERVER, int(SMTP_PORT)) as server:
        server.starttls()  # TLS aktivieren
        server.login(SMTP_USER, SMTP_PASS)
        print("SMTP-Authentifizierung erfolgreich!")
except Exception as e:
    print(f"Fehler bei der SMTP-Verbindung: {e}")
    sys.exit(1)
