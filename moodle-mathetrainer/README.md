# Moodle Mathetrainer (Deutschland)

Startstruktur für ein erweiterbares Moodle-Plugin-System zur Generierung, Auswertung und didaktischen Einordnung von Mathematikübungen.

## Ziel

Das Projekt trennt sauber zwischen:

- **`mod_mathetrainer`**: Moodle-Aktivität für Unterricht und Lernende
- **`local_curriculum_math`**: Zentrales Curriculum- und Kompetenzmodell (KMK + Länderprofile)

## Fokus (Phase 1)

- Fach: Mathematik
- Land: Deutschland
- Länderprofile: NRW, Baden-Württemberg
- Stufen: Primarstufe, Sekundarstufe I
- Schwerpunkt Klassen: 5–7
- Themen: natürliche Zahlen, Brüche, Dezimalzahlen, rationale Zahlen, einfache Gleichungen

## Leitprinzipien

- Moodle-konforme Plugin-Architektur
- Deterministische und prüfbare Aufgabenerzeugung
- Kompetenz-Tagging nach KMK-Modell
- Lehrkraft-Filter nach Bundesland, Schulform, Klasse, Thema, Kompetenz, AFB
- Datenschutz by Design (keine personenbezogenen Schülerdaten an KI-Dienste)
- KI zunächst nur optional und unterstützend für Erklärungen

## Verzeichnisübersicht

Siehe Architektur-Dokumentation unter `docs/architecture.md`.

## Status

Version 0.3.0 (Alpha), noch nicht in einer echten Moodle-Instanz getestet.

- Lehrkraft legt Bundesland, Klasse, Thema, AFB und optional eine Kompetenz fest
- Mit installiertem `local_curriculum_math` und importierten Packs: Kompetenz per Auswahlliste;
  die im Pack hinterlegten `exercise_generators` bestimmen dann die Aufgabentypen
- Ohne Kompetenz: Auswahl nach Thema, Klasse und AFB (mit schrittweiser Lockerung, damit nie leer)
- Lernende bekommen pro Durchgang 5 generierte Aufgaben und sofort Rückmeldung mit Fehlerdiagnose
- Lehrkräfte sehen Durchgänge, Durchschnitt pro Person und häufige Fehler
- Lernstand je Kompetenz (`mathetrainer_mastery`), Privacy API, Kurs-Reset

Aufgabentypen (deterministisch, gleicher Seed = gleiche Aufgabe):

| Template | Thema | Klasse | AFB | Inhalt | Erkannte Fehler |
|---|---|---|---|---|---|
| `fraction_numberline` | Brüche | 5–6 | I | Bruch am Zahlenstrahl ablesen (0–1, 0–2) | Striche statt Abschnitte gezählt, falsches Ganzes |
| `fraction_compare` | Brüche | 6–7 | I–II | Brüche vergleichen (<, =, >) | größerer Nenner = größerer Bruch, nur Zähler verglichen, gleichwertige Brüche |
| `integer_numberline` | Rationale Zahlen | 5–7 | I | Ganze Zahl am Zahlenstrahl ablesen, Skala selbst erschließen | Skala ignoriert, Vorzeichen |
| `rational_addition` | Rationale Zahlen | 7–8 | I–II | Addition/Subtraktion mit negativen Zahlen und Brüchen | Vorzeichen, Minus vor negativer Zahl, Beträge addiert, Nenner addiert |
| `sign_error_analysis` | Rationale Zahlen | 7–8 | II–III | Fehler in einer Rechnung finden und richtig rechnen | falsches Ergebnis übernommen + wie oben |
| `linear_equation` | Gleichungen | 7–8 | I–II | `ax + b = c` lösen | falsche Gegenrechnung, Teilen vergessen, multipliziert statt geteilt |

Noch offen:

- Test in einer Moodle-Instanz (Installation, Formular, Durchgang, Privacy)
- Curriculum-Packs um Gleichungen und Klasse 5 ergänzen
- Backup/Restore, Bewertung im Gradebook
- YAML-Import braucht die PHP-Erweiterung `yaml` (alternativ JSON-Packs)
- Keine produktive KI-Anbindung, kein Vollimport offizieller Lehrpläne

## Tests

PHPUnit-Tests liegen unter `mod/mathetrainer/tests/` (Moodle-Standard):

```
vendor/bin/phpunit --testsuite mod_mathetrainer_testsuite
```

## Entwicklung

Der empfohlene Entwicklungsablauf ist in `docs/devflow.md` beschrieben.
