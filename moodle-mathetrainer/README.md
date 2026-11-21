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

Version 0.2.0 (Alpha). Die Aktivität ist in Moodle anlegbar und für Lernende nutzbar:

- Lehrkraft legt Bundesland, Klasse, Thema, AFB und optional eine Kompetenz-ID fest
- Lernende bekommen pro Durchgang 5 generierte Aufgaben und sofort Rückmeldung
- Fehlerdiagnose statt nur „falsch“ (z. B. Vorzeichenfehler, Nenner addiert, „größerer Nenner = größerer Bruch“)
- Lehrkräfte sehen Durchgänge, Durchschnitt pro Person und häufige Fehler
- Lernstand je Kompetenz (`mathetrainer_mastery`), Privacy API, Kurs-Reset

Aufgabentypen (deterministisch, gleicher Seed = gleiche Aufgabe):

| Template | Thema | Klasse | Inhalt |
|---|---|---|---|
| `rational_addition` | Rationale Zahlen | 7–8 | Addition/Subtraktion mit negativen ganzen Zahlen und Brüchen |
| `fraction_compare` | Brüche | 6–7 | Brüche vergleichen (<, =, >), inkl. gleichwertiger Brüche |

Noch offen:

- Weitere Generatoren aus den Packs (`integer_numberline`, `fraction_numberline`, `sign_error_analysis`, Gleichungen)
- Aufgabenauswahl über `local_curriculum_math` statt über die Registry im Modul
- Backup/Restore, Bewertung im Gradebook
- Keine produktive KI-Anbindung, kein Vollimport offizieller Lehrpläne

## Tests

PHPUnit-Tests liegen unter `mod/mathetrainer/tests/` (Moodle-Standard):

```
vendor/bin/phpunit --testsuite mod_mathetrainer_testsuite
```

## Entwicklung

Der empfohlene Entwicklungsablauf ist in `docs/devflow.md` beschrieben.
