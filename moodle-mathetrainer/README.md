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

Diese Version ist ein **Skeleton/Bootstrap** für die weitere Entwicklung:

- Grundstruktur vorhanden
- Datenmodell-Entwürfe angelegt
- Beispielhafte Curriculum-Packs als YAML
- Keine produktive KI-Anbindung
- Kein Vollimport offizieller Lehrpläne

## Entwicklung

Der empfohlene Entwicklungsablauf ist in `docs/devflow.md` beschrieben.
