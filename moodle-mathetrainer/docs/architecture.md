# Architektur

## Plugins

### `mod_mathetrainer` (Aktivität)
- **Generatoren** (`classes/generator/`): ein Klasse je Aufgabentyp, rein (keine Datenbank, keine
  Sprachstrings), deterministisch über einen Seed. Liefern Aufgabe (`payload`) und Erwartung
  (`expected`: Lösung + typische Fehlantworten mit Fehlerkategorie).
- **Registry** (`generator/registry.php`): Aufgabentypen mit Thema, Klassen, AFB; Auswahl mit
  schrittweiser Lockerung, damit eine Aktivität nie leer ist.
- **Validator** (`validator/answer_validator.php`): prüft Eingaben exakt (Brüche, Dezimalzahlen,
  typografisches Minus), erkennt Fehlerkategorien, prüft geforderte Schreibweise.
- **Durchgänge** (`local/attempt_manager.php`): Durchgang starten, Aufgaben erzeugen, abgeben, Lernstand.
- **Bewertung** (`local/grading.php`, `lib.php`): Gradebook mit Punkten oder Skala, Methode
  bester/letzter/erster/Durchschnitt.
- **Bericht** (`report.php`, `local/report_data.php`, `local/recommender.php`): Ergebnisse je
  Lernende und Aufgabentyp, typische Fehler, regelbasierte Empfehlungen, Download.
- **Bloom-Pakete** (`classes/bloom/package_builder.php`, `cli/bloom_package.php`): siehe
  `bloom-integration.md`.
- Moodle-Integration: Backup/Restore, Privacy API, Kurs-Reset, Events, Gruppen im Bericht.

### `local_curriculum_math` (Lehrplanmodell)
- Import von Curriculum-Packs (YAML/JSON, eigener YAML-Leser ohne `ext-yaml`)
- Kompetenzen, Zuordnung zu KMK, Verknüpfung mit Aufgabentypen
- Abfragen für die Aktivität (`mapper/competency_query_service.php`)

Die Aktivität funktioniert auch ohne das Lehrplan-Plugin (Auswahl nach Thema/Klasse/AFB).

## Datenfluss

1. Lehrkraft konfiguriert die Aktivität: Kompetenz aus dem Lehrplan **oder** Thema, Klasse, AFB.
2. Die Aktivität ermittelt die Aufgabentypen (Kompetenz → `exercise_generators`, sonst Registry).
3. Generatoren erzeugen 5 Aufgaben je Durchgang (zufälliger Seed, Aufgabe wird gespeichert).
4. Lernende antworten; der Validator bewertet und bestimmt Fehlerkategorien.
5. Ergebnis → Gradebook, Lernstand je Kompetenz, Empfehlung für die Lernenden.
6. Lehrkraft sieht Bericht je Lernende und Aufgabentyp und lädt ihn bei Bedarf herunter.
7. Optional: Lehrkraft erzeugt aus denselben Aufgabentypen ein Bloom-Lernpaket.

## Datenschutz
- Keine personenbezogenen Daten an externe Dienste (auch nicht an Bloom: nur Aufgaben).
- Keine KI in der Bewertung; alles lokal, deterministisch, nachvollziehbar.
- Privacy API: Export und Löschung für Durchgänge, Antworten, Lernstand.

## Qualitätssicherung
- PHPUnit: reine Tests (Generatoren, Validator, Bewertung, Empfehlungen, YAML) und
  Datenbanktests (Durchgang, Gradebook, Bericht, Privacy, Backup/Restore)
- GitHub Actions mit `moodle-plugin-ci` (Moodle 4.5 und 5.3, PostgreSQL): Lint, Coding Standard,
  Validierung, Savepoints, PHPUnit
