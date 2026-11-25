# Datenmodell

## local_curriculum_math

| Tabelle | Inhalt | Befüllt durch |
|---|---|---|
| `curriculum_framework` | Ein Rahmen je Pack (z. B. `NRW-MATH-SI`, Version `draft-0.2`), Land, Stufe | Import (Upsert über ID + Version) |
| `curriculum_competency` | Kompetenzen mit Kurzlabel für Lehrkraft und Lernende, Klassen, Thema, KMK-Leitidee, Prozesskompetenzen, AFB | Import (Upsert) |
| `curriculum_mapping` | Zuordnung Landeskompetenz → KMK-Kompetenz (`kmk.maps_to`) | Import (ersetzt je Kompetenz) |
| `exercise_template` | Aufgabentypen, die in Packs genannt werden (`exercise_generators`) | Import |
| `exercise_competency_link` | Aufgabentyp ↔ Kompetenz | Import |
| `curriculum_source`, `curriculum_node` | Vorgesehen für Quellenverweise und Gliederungsknoten | noch nicht genutzt |

## mod_mathetrainer

| Tabelle | Inhalt | Personenbezug |
|---|---|---|
| `mathetrainer` | Aktivität: Bundesland, Schulform, Klasse, Thema, AFB, Kompetenz-ID, Bewertung (`grade`), Bewertungsmethode (`grademethod`) | nein |
| `mathetrainer_attempts` | Durchgänge (Nummer, Status, Anteil richtig) | ja |
| `mathetrainer_items` | Erzeugte Aufgaben je Durchgang: Template, Seed, Aufgabe (`payloadjson`), Lösung und Diagnosen (`expectedjson`) | indirekt (über Durchgang) |
| `mathetrainer_responses` | Antworten: Eingabe, richtig/falsch, Fehlerkategorie | ja |
| `mathetrainer_mastery` | Lernstand je Lernende und Kompetenz (laufender Mittelwert) | ja |
| `mathetrainer_generation_log` | Protokoll der Aufgabenerzeugung (Template, Seed) | nein |

Die Aufgabenerzeugung ist deterministisch: Aus Template und Seed lässt sich jede Aufgabe exakt
wiederherstellen. Gespeichert wird trotzdem die erzeugte Aufgabe, damit spätere Änderungen an einem
Generator alte Durchgänge nicht verändern.

## Versionen und Upgrades

- `mod_mathetrainer` 2026112500: Felder `grade` und `grademethod`; bestehende Aktivitäten erhalten ihre
  Gradebook-Spalte über eine Hintergrundaufgabe (`\mod_mathetrainer\task\update_all_grades`), weil das
  Gradebook während eines Upgrades nicht geändert werden darf.
