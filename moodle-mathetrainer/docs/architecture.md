# Architektur

## Plugin-Trennung

## 1) `mod_mathetrainer` (Activity Plugin)
Verantwortlich für:
- Aufgabengenerierung aus Templates
- Antwortprüfung und Feedback
- Versuche, Items, Responses, Mastery-Tracking
- Lehrkraftansichten und Kompetenzberichte

## 2) `local_curriculum_math` (Local Plugin)
Verantwortlich für:
- Verwaltung von Frameworks (KMK, NRW, BW)
- Knoten/Kompetenzen/Mappings
- Curriculum-Versionierung
- Import aus YAML/JSON
- Verknüpfung von Kompetenzen mit Aufgabentemplates

## Datenfluss (vereinfacht)
1. Lehrkraft konfiguriert Aktivität (`mod_mathetrainer`) mit Filtern.
2. Aktivität fragt `local_curriculum_math` nach passenden Kompetenzen/Templates.
3. Generator erzeugt deterministische Aufgabeninstanzen.
4. Lernende bearbeiten Aufgaben; Antworten werden geprüft.
5. Ergebnisse werden auf Kompetenzebene aggregiert.
6. Lehrkräfte sehen Fortschritt nach Kompetenz/AFB/Topic.

## Datenschutz
- Keine personenbezogenen Schülerdaten an externe KI.
- KI-Nutzung optional, nur für erklärende Texte.
- Aufgabenkernlogik lokal, deterministisch, auditierbar.

## Erweiterbarkeit
- Neue Länderprofile über zusätzliche Curriculum-Packs
- Neue Übungsarten über Generator/Validator-Klassen
- Weitere Stufen und Jahrgänge über Framework-Versionen
