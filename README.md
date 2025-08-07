# Secure-PHP-CAPTCHA

Dieses Projekt implementiert ein **6-Schritte-CAPTCHA** in PHP ohne JavaScript.  

## Funktionsweise

- Das CAPTCHA erzeugt 6 zufällige Zeichen (A–Z, 0–9) **einmalig zu Beginn** und speichert sie in der Session.  
- Nutzer geben nacheinander in 6 Schritten jeweils ein Zeichen ein.  
- Es ist **immer nur ein Eingabefeld aktiv**, vorherige Zeichen können nicht geändert werden.  
- Nach dem 6. Zeichen wird das Formular zur Überprüfung weitergeleitet.  
- Der Captcha-Code wird in einer Bilddatei (`generate.php`) mit Störungen, Rauschen, Rotation und Blur angezeigt.

## Dateien

- `index.php`: Mehrschritt-Formular, Verwaltung der Session, Eingabe und Navigation.  
- `generate.php`: Erzeugt Captcha-Bild für jedes der 6 Zeichen aus der Session.  
- `verify.php`: Überprüft, ob die Eingaben mit den erzeugten Zeichen übereinstimmen.

## Setup

1. Stelle sicher, dass die GD-Library in PHP aktiviert ist.  
2. Lege eine TrueType-Schriftdatei `arial.ttf` in den Projektordner (für saubere Schrift im Captcha).  
3. Öffne `index.php` im Browser und durchlaufe die 6 Schritte.

## Sicherheit

- CSRF-Schutz mit Token.  
- Session-Hardening und Fingerprint (IP & UserAgent) zur Verhinderung von Session-Hijacking.  
- Keine Möglichkeit zurückzuspringen oder Felder zu überspringen.

## Anpassungen

- Schriftfarbe, Rauschintensität, Störlinien können in `generate.php` angepasst werden.  
- CAPTCHA-Zeichenlänge (aktuell 6) kann in `index.php` und `generate.php` leicht geändert werden.

 <img src="img/Screenshot 2025-08-08 002358.png" width="300"/>
