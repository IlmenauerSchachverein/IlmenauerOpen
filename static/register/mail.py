import os
from dotenv import load_dotenv
import sys
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart

load_dotenv("/var/private/isv/open.env")

SMTP_SERVER = os.getenv("SMTP_SERVER")
SMTP_PORT = int(os.getenv("SMTP_PORT"))
SMTP_USER = os.getenv("SMTP_USER")
SMTP_PASS = os.getenv("SMTP_PASS")

FROM_EMAIL = os.getenv("FROM_EMAIL")
BCC_RECIPIENTS = os.getenv("BCC_RECIPIENTS").split(",")

def send_email(to_email, vorname, nachname, verein, geburtsdatum, telefon, fide_id):
    subject = "Anmeldebestätigung - Ilmenauer Open 2025"
    message = f"""\

Hallo {vorname} {nachname},

vielen Dank für Ihre Anmeldung für das Ilmenauer Open 2025. Hier sind Ihre übermittelten Daten:

- E-Mail-Adresse: {to_email}
- Verein: {verein}
- Geburtsdatum: {geburtsdatum}
- Telefonnummer: {telefon}
- FIDE-ID: {fide_id}

Bitte überprüfen Sie Ihre Angaben. Falls etwas nicht stimmt, kontaktieren Sie uns.

Mit freundlichen Grüßen,  
Ilmenauer Schachverein
"""

    msg = MIMEMultipart()
    msg['From'] = FROM_EMAIL
    msg['To'] = to_email
    msg['Subject'] = subject
    msg.attach(MIMEText(message, 'plain'))

    
    try:
        with smtplib.SMTP(SMTP_SERVER, SMTP_PORT) as server:
            server.starttls()  
            server.login(SMTP_USER, SMTP_PASS)
            server.sendmail(FROM_EMAIL, [to_email] + BCC_RECIPIENTS, msg.as_string())
        print("E-Mail erfolgreich gesendet!")
    except Exception as e:
        print(f"Fehler beim Senden der E-Mail: {e}")

if __name__ == "__main__":
    if len(sys.argv) != 8:
        print("Verwendung: python mail.py <Empfänger> <Vorname> <Nachname> <Verein> <Geburtsdatum> <Telefonnummer> <FIDE-ID>")
        sys.exit(1)
    
    
    to_email = sys.argv[1]
    vorname = sys.argv[2]
    nachname = sys.argv[3]
    verein = sys.argv[4]
    geburtsdatum = sys.argv[5]
    telefon = sys.argv[6]
    fide_id = sys.argv[7]
    
    send_email(to_email, vorname, nachname, verein, geburtsdatum, telefon, fide_id)
