import os
from dotenv import load_dotenv
import sys
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

# .env-Datei laden
load_dotenv("/var/private/isv/open.env")

# SMTP-Konfiguration aus .env-Datei
SMTP_SERVER = os.getenv("SMTP_SERVER")
SMTP_PORT = int(os.getenv("SMTP_PORT"))
SMTP_USER = os.getenv("SMTP_USER")
SMTP_PASS = os.getenv("SMTP_PASS")


print("SERVER", SMTP_SERVER)
print("Port",SMTP_PORT)
print("User",SMTP_USER)
print("Pass",SMTP_PASS)
import sys
sys.exit(0)

# Debugging: Zeige die geladenen Umgebungsvariablen an
print(f"""
Geladene Umgebungsvariablen:
- SMTP_SERVER: {SMTP_SERVER}
- SMTP_PORT: {SMTP_PORT}
- SMTP_USER: {SMTP_USER}
- SMTP_PASS: {SMTP_PASS[:2]}*** (gekürzt)
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

# E-Mail-Inhalt
message = f"""
Hallo {vorname} {nachname},

vielen Dank für Ihre Anmeldung. Hier sind Ihre übermittelten Daten:

- Verein: {verein}
- Geburtsdatum: {geburtsdatum}
- Telefonnummer: {telefonnummer}
- FIDE-ID: {fide_id}

Bitte überprüfen Sie Ihre Angaben. Falls etwas nicht stimmt, kontaktieren Sie uns.

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
