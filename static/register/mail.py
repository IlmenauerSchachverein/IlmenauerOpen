import sys
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

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

# SMTP-Konfiguration
SMTP_SERVER = "smtp.mailbox.org"
SMTP_PORT = 587
SMTP_USER = "ilmenauer.open@ilmenauersv.de"  # Benutzername
SMTP_PASS = "dein_passwort"  # Passwort

# Funktion zum Senden der E-Mail
def send_email(to_email, vorname, nachname, verein, geburtsdatum, telefonnummer, fide_id, rabatt, bestaetigung, agb, blitzturnier):
    from_email = "info@ilmenauer-schachverein.de"
    subject = "Anmeldebestätigung"
    
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

Bitte überprüfen Sie Ihre Angaben. Falls etwas nicht stimmt, kontaktieren Sie uns.

Mit freundlichen Grüßen,
Ihr Team
"""

    # E-Mail erstellen
    msg = MIMEMultipart()
    msg['From'] = from_email
    msg['To'] = to_email
    msg['Subject'] = subject
    msg.attach(MIMEText(message, 'plain'))

    # Verbindung zum SMTP-Server herstellen und E-Mail senden
    try:
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
            server.starttls()  # TLS aktivieren
            server.login(SMTP_USER, SMTP_PASS)
            server.sendmail(from_email, to_email, msg.as_string())
        print("E-Mail erfolgreich gesendet!")
    except Exception as e:
        print(f"Fehler beim Senden der E-Mail: {e}")
        sys.exit(1)

# E-Mail senden
send_email(
    to_email, vorname, nachname, verein, geburtsdatum,
    telefonnummer, fide_id, rabatt, bestaetigung, agb, blitzturnier
)
