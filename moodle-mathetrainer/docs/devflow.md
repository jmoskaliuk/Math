# Devflow (Moodle Mathetrainer)

Dieser Devflow beschreibt die praktische Entwicklungsroutine für `mod_mathetrainer` und `local_curriculum_math`.

## 1) Produkt- und Didaktik-Frame pro Feature

Jedes Feature startet mit einer kurzen Feature-Card (Issue/Task):

- Zielgruppe: Schüleransicht / Lehreransicht / Admin
- Bundesland(e): NRW, BW (später erweiterbar)
- Schulform, Klassenstufe, Thema
- Kompetenzbezug (KMK + Landesmapping)
- Anforderungsbereich (AFB I/II/III)
- Datenschutz-Auswirkung
- Akzeptanzkriterien (funktional + didaktisch)

## 2) Architektur-Entscheidung: mod vs local

- `local_curriculum_math` enthält **Curriculumdaten, Mapping, Import, Versionierung**.
- `mod_mathetrainer` enthält **Aktivität, Aufgabenerzeugung, Antwortprüfung, Fortschritt/Reporting**.
- Keine Vermischung von Zuständigkeiten; Kommunikation über klar definierte Services.

## 3) Daten zuerst (Schema + Migration)

1. Tabellen-/Feldänderung in `db/install.xml`
2. Upgrade-Schritt in `db/upgrade.php` ergänzen
3. Version in `version.php` erhöhen
4. Datenzugriff/Repository-Klassen anpassen

Regel: Keine neue Business-Logik ohne persistentes Datenmodell.

## 4) Curriculum-Flow

1. YAML/JSON-Pack in `curriculum-packs/<state>/...`
2. Validierung (Pflichtfelder, IDs, Version, Mapping)
3. Import in `local_curriculum_math`
4. Mapping-Tests (Landeskompetenz -> KMK)

Wichtig: Keine langen Original-Lehrplantexte speichern; stattdessen kurze Labels + Quellenverweise.

## 5) Exercise-Flow (deterministisch)

1. Template wählen (`exercise_template` + Kompetenzlink)
2. Seed-basiert Aufgabe erzeugen
3. Erwartete Lösung strukturiert speichern
4. Antwort validieren und Fehlerkategorie bestimmen
5. Feedback/Erklärung aus regelbasierten Bausteinen ableiten

Regel: Korrektheit darf nicht von KI abhängen.

## 6) Teacher-Flow

Bei jedem neuen Filter-/Report-Feature sicherstellen:

- Filterbar nach Bundesland, Schulform, Klasse, Thema, Kompetenz, AFB, Schwierigkeit
- Kompetenzstatus nachvollziehbar (nicht nur Punktwert)
- Häufige Fehlertypen und Wiederholungsempfehlung sichtbar

## 7) Privacy & Security Gates (Definition of Done)

Vor Merge muss erfüllt sein:

- Keine personenbezogenen Schülerdaten an externe KI
- Rollen/Capabilities geprüft
- Datenminimierung im Schema dokumentiert
- Privacy API-Konzept aktualisiert
- Secrets nur serverseitig

## 8) Qualitäts- und Review-Flow

Minimal pro PR:

- PHP Syntax (`php -l`)
- XMLDB-Konsistenz (Moodle-konform)
- Reviewer-Check auf Plugin-Grenzen (mod vs local)
- Prüfen, ob neue Strings in `lang/de/*` vorhanden sind
- Docs aktualisiert (`architecture.md`, `data-model.md`, ggf. `privacy.md`)

## 9) Branching & Commits

Empfohlene Branch-Namen:

- `feature/mod-generator-fractions`
- `feature/local-importer-yaml`
- `fix/mod-response-validation`

Commit-Format:

- `mod: add deterministic fraction comparison generator`
- `local: add curriculum YAML validation for NRW`
- `docs: define MVP devflow and review gates`

## 10) MVP-Umsetzung in Inkrementen

### Inkrement A – Fundament ✅
- Plugin-Struktur
- XMLDB-Schema
- KMK/NRW/BW Beispielpacks

### Inkrement B – Curriculum nutzbar ✅
- YAML/JSON-Importer
- Framework/Competency-Mapping Services
- Filter-Queries

### Inkrement C – Lernen nutzbar ✅
- 3–5 deterministische Generatoren (Kl. 5–7)
- Antwortprüfung + Feedbackbausteine
- Versuchsspeicherung

### Inkrement D – Lehrkraftnutzen ✅
- Kompetenzreport je Schüler/Gruppe
- Fehlertypanalyse
- Wiederholungsempfehlungen

## 11) Definition of Done (MVP-Feature)

Ein Feature ist „done“, wenn:

1. Fachlich korrekt (Mathe)
2. Kompetenzbezug vorhanden
3. Deterministisch prüfbar
4. Moodle-konform integriert
5. Datenschutzanforderungen erfüllt
6. Dokumentation aktualisiert

## 12) Parallelisierung offener Themen

Für paralleles Arbeiten im Team:

- **Track A (Curriculum):** Importer, Validierung, Mapping-Qualität
- **Track B (Exercises):** Generatoren, Validatoren, Fehlertypen
- **Track C (Reporting):** Kompetenzstatus, Fehleranalysen, Lehrkraftansichten
- **Track D (Platform):** Privacy API, Capabilities, Release/Upgrade-Stabilität

Synchronisation:

- Gemeinsames Weekly Schema-Review
- Gemeinsame Definition der Generator-/Template-IDs
- Gemeinsame Abnahme je Kompetenzpaket (NRW/BW)
