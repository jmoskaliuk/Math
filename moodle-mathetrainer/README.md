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

Version 0.5.0 (Alpha). **Getestet in Moodle 4.5 mit PostgreSQL**: Installation, Upgrade, Import, Durchgang
im Browser, Gradebook, Bericht, Backup/Restore, Privacy API (64 PHPUnit-Tests), Moodle-Coding-Standard ohne Befund.

- Lehrkraft legt Bundesland, Schulform, Klasse, Thema, AFB und optional eine Kompetenz fest
- Mit installiertem `local_curriculum_math` und importierten Packs: Kompetenz per Auswahlliste;
  die im Pack hinterlegten `exercise_generators` bestimmen dann die Aufgabentypen
- Lernende bekommen pro Durchgang 5 generierte Aufgaben, sofort Rückmeldung mit Fehlerdiagnose und
  eine Übungsempfehlung
- **Gradebook**: Punkte oder Skala, Bewertungsmethode bester/letzter/erster Durchgang oder Durchschnitt
- **Bericht** für Lehrkräfte: je Lernende und Aufgabentyp, Klassenübersicht, typische Fehler,
  Empfehlungen, Gruppen, Download (CSV, Excel, ODS …)
- **Bloom-Lernpakete**: aus den Aufgabentypen einer Aktivität ein Paket für Bloom erzeugen, typische
  Fehler werden zu Multiple-Choice-Optionen mit Erklärung (siehe `docs/bloom-integration.md`)
- Lernstand je Kompetenz, Privacy API, Kurs-Reset, Backup/Restore

Aufgabentypen (deterministisch, gleicher Seed = gleiche Aufgabe):

| Template | Thema | Klasse | AFB | Inhalt | Erkannte Fehler |
|---|---|---|---|---|---|
| `order_of_operations` | Natürliche Zahlen | 5–6 | I–II | Punkt vor Strich, Klammern | von links nach rechts gerechnet, Klammer ignoriert |
| `decimal_convert` | Dezimalzahlen | 6–7 | I–II | Bruch ↔ Dezimalzahl (abbrechende Dezimalzahlen) | Ziffern abgeschrieben (3/4 → 0,34), Stellenwertfehler (0,35 → 35/10), falsche Schreibweise |
| `fraction_numberline` | Brüche | 5–6 | I | Bruch am Zahlenstrahl ablesen (0–1, 0–2) | Striche statt Abschnitte gezählt, falsches Ganzes |
| `fraction_compare` | Brüche | 6–7 | I–II | Brüche vergleichen (<, =, >) | größerer Nenner = größerer Bruch, nur Zähler verglichen, gleichwertige Brüche |
| `integer_numberline` | Rationale Zahlen | 5–7 | I | Ganze Zahl am Zahlenstrahl ablesen, Skala selbst erschließen | Skala ignoriert, Vorzeichen |
| `rational_addition` | Rationale Zahlen | 7–8 | I–II | Addition/Subtraktion mit negativen Zahlen und Brüchen | Vorzeichen, Minus vor negativer Zahl, Beträge addiert, Nenner addiert |
| `sign_error_analysis` | Rationale Zahlen | 7–8 | II–III | Fehler in einer Rechnung finden und richtig rechnen | falsches Ergebnis übernommen + wie oben |
| `linear_equation` | Gleichungen | 7–8 | I–II | `ax + b = c` lösen | falsche Gegenrechnung, Teilen vergessen, multipliziert statt geteilt |

Noch offen:

- Fachliche Prüfung der Packs (Kurzlabels, Klassenzuordnungen, Leitidee für Gleichungen)
- Bloom-Pilot (Pakete in Bloom anlegen, als SCORM in Moodle nutzen)
- Lizenz klären: Moodle verlangt für das Plugin-Verzeichnis GPL v3 (Repo ist MIT)
- Keine produktive KI-Anbindung, kein Vollimport offizieller Lehrpläne

## Curriculum importieren

YAML-Packs werden mit `ext-yaml` gelesen, falls installiert, sonst mit einem eingebauten Leser
(Teilmenge von YAML, ausreichend für die Packs). Erneutes Importieren aktualisiert vorhandene Einträge.

```
php local/curriculum_math/cli/import_curriculum.php --dir=/pfad/zu/curriculum-packs
```

Die Packs enthalten pro Landeskompetenz die passenden Aufgabentypen (`exercise_generators`) und die
Zuordnung zur KMK-Kompetenz (`kmk.maps_to`, gespeichert in `curriculum_mapping`).

## Tests und CI

GitHub Actions (`.github/workflows/moodle-plugin-ci.yml`) prüft beide Plugins mit `moodle-plugin-ci`
in Moodle 4.1 und 4.5: PHP-Lint, Coding Standard (`phpcs.xml`), PHPDoc, Validierung, Savepoints, PHPUnit.


PHPUnit-Tests liegen unter `mod/mathetrainer/tests/` (Moodle-Standard):

```
vendor/bin/phpunit --testsuite mod_mathetrainer_testsuite
vendor/bin/phpunit --testsuite local_curriculum_math_testsuite
```

## Entwicklung

Der empfohlene Entwicklungsablauf ist in `docs/devflow.md` beschrieben.
