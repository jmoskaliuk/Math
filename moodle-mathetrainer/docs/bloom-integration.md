# Konzept: Mathetrainer × Bloom

Stand: November 2026 · Status: Paket-Generator fertig, Pilot in Bloom angelegt (Weg A)

## Idee in einem Satz

Der **Mathetrainer erzeugt** fachlich geprüfte Aufgaben mit Fehlerdiagnose, **Bloom macht daraus
Lernpakete**, die Lehrkräfte ohne Programmierung anpassen, und **Moodle liefert sie aus** und
sammelt die Ergebnisse.

## Wer macht was

| | Mathetrainer (dieses Repo) | Bloom | Moodle |
|---|---|---|---|
| Aufgabe | Aufgaben erzeugen, Lösung und typische Fehler berechnen, Lehrplanbezug | Didaktische Hülle: Einstieg, Erklärungen, Reihenfolge, Gestaltung, Prüfen und Freigabe | Auslieferung an Klassen, Noten, Berichte |
| Wer arbeitet damit | Fachdidaktik / Entwicklung (Generatoren), Lehrkraft (Auswahl) | Lehrkraft, Fachschaft, Mediengestaltung | Lernende, Lehrkraft |
| Stärke | Unbegrenzt viele korrekte Varianten, Fehlerdiagnose, KMK-/Länderbezug | Schnelles Anpassen im Editor, Versionen, Vorlagen, Export (SCORM, HTML, Moodle-XML, GIFT) | Kursorganisation, Gradebook |

Der Mathetrainer bleibt als Moodle-Aktivität bestehen (adaptives Üben mit immer neuen Zahlen).
Bloom-Pakete sind die **kuratierte, gestaltete** Variante: feste Aufgabenfolge mit Erklärungen, die
eine Lehrkraft für ihre Klasse zusammenstellt.

## Was Bloom heute schon kann (geprüft über die Bloom-Schnittstelle)

- Projekte (Lerneinheiten) mit **Konzept**: Zielgruppe, Lernziele mit Taxonomiestufe, Kernaussagen,
  Tonfall, Anrede (du/Sie)
- 17 Folientypen, für Mathe relevant:
  - `mc`: Multiple Choice mit **Rückmeldung pro Antwortoption**
  - `input` (Modus `short`): Kurzantwort mit **Liste akzeptierter Antworten**
  - `cloze`: Lückentext, `sequence` / `order`: Reihenfolge, `selfcheck`: Selbsteinschätzung
  - `info`: Erklärungen mit Kacheln, Tipps, Merksatz; `video`; `result`: Gesamtergebnis
- Bilder an Folien (Upload), Vorlesestimme (Narration)
- Versionen („Über MCP geändert“ ist im Editor rückgängig zu machen), Status Entwurf → Review → Freigegeben
- Export: **SCORM 1.2/2004**, HTML, Vorschau-Link, **Moodle-XML und GIFT** (Fragen für die Moodle-Fragensammlung)
- In der Organisation gibt es bereits Mathe-Projekte („Bruchrechnen Basics“, „Bruchrechnen 2/3“) –
  das Muster Info → Aufgaben → Ergebnis passt genau zu dem, was der Generator erzeugt.

## Der Kern: Fehlvorstellungen werden zu Distraktoren

Jede Mathetrainer-Aufgabe kennt nicht nur die richtige Lösung, sondern auch die **typischen falschen
Ergebnisse** und warum sie entstehen. Bloom-Multiple-Choice kann pro Antwortoption eine Rückmeldung
geben. Zusammen ergibt das Aufgaben, bei denen jede falsche Antwort eine **gezielte Erklärung** auslöst:

> **Berechne 1/3 + 1/4**
> - 7/12 ✔ „Richtig!“
> - 2/7 ✘ „Du hast Zähler und Nenner einzeln addiert. Bringe die Brüche zuerst auf einen gemeinsamen Nenner.“
> - −7/12 ✘ „Der Betrag stimmt, aber das Vorzeichen ist falsch.“

Das ist didaktisch deutlich stärker als zufällige Distraktoren und kostet die Lehrkraft keine Arbeit.

### Abbildung Aufgabentyp → Bloom-Folie

| Mathetrainer | Bloom-Folie | Details |
|---|---|---|
| Zahlenaufgabe (`numeric`) | `input` (short) **oder** `mc` | Input: alle Schreibweisen der Lösung als akzeptierte Antworten (−3/4, -3/4, −0,75 …). MC: Lösung + bis zu 3 Fehlvorstellungen mit Erklärung |
| Auswahlaufgabe (`choice`, z. B. Brüche vergleichen) | `mc` | <, =, > mit Fehlerrückmeldung je Zeichen |
| Zahlenstrahl | wie oben + **Bild** | Zahlenstrahl als SVG im Paket (`assets`), wird in Bloom als Bild hochgeladen |
| Umwandeln Bruch ↔ Dezimalzahl | `input` / `mc` | Nur die geforderte Schreibweise wird akzeptiert |
| Paket-Anfang | `info` | Was geübt wird, Merksatz je Aufgabentyp, Tipp |
| Paket-Ende | `result` | Ergebnis aus allen bewerteten Folien |
| Lehrplanbezug | Konzept | Lernziele je Aufgabentyp mit Taxonomiestufe (verstehen / anwenden / analysieren), Kernaussagen, Kompetenz-ID als Notiz |

## Ablauf für Lehrkräfte

```
 Mathetrainer (Moodle)              Bloom                              Moodle-Kurs
 ─────────────────────              ─────                              ───────────
 1. Kompetenz / Thema, Klasse,
    AFB wählen
 2. „Bloom-Paket erzeugen“  ──────▶ 3. Neues Projekt (Entwurf) mit
                                       Konzept, Einstieg, Aufgaben,
                                       Ergebnis
                                    4. Lehrkraft passt an:
                                       Texte, Erklärfolie, Video,
                                       Aufgaben tauschen/löschen,
                                       Reihenfolge, Gestaltung
                                    5. Prüfen → Freigeben
                                    6. Export SCORM ───────────────▶ 7. SCORM-Aktivität:
                                       (oder Moodle-XML ──────────▶    Note ins Gradebook
                                        in die Fragensammlung)        (oder Test mit Fragen)
```

Varianten für Parallelklassen oder Nachschreiber: gleiches Paket mit anderem `seed` erzeugen –
identische Struktur, andere Zahlen.

## Integrationswege

| Weg | Wie | Heute möglich? | Aufwand |
|---|---|---|---|
| **A. Über Claude und die Bloom-Schnittstelle (MCP)** | Paket erzeugen (CLI oder Moodle), Claude legt das Projekt in Bloom an (`create_project`, `create_slides`, `upload_image`) | **Ja** – Generator ist fertig, Bloom-Werkzeuge sind verfügbar | gering |
| B. Datei-Import in Bloom | Lehrkraft lädt die JSON-Datei in Bloom hoch | Nein – Bloom bräuchte einen Import für dieses Format | mittel (Bloom-Seite) |
| C. Direkt aus Moodle | Button im Mathetrainer ruft eine Bloom-API auf (Organisation + Token in den Plugin-Einstellungen) | Nein – braucht eine Bloom-API für Server-zu-Server mit Anmeldung | mittel (beide Seiten) |

**Empfehlung:** Mit **A** einen Pilot machen (2–3 Pakete, eine Fachschaft), daraus die Anforderungen
an **C** ableiten. B ist ein guter Zwischenschritt, falls Bloom ohnehin einen Projekt-Import bekommt.

## Was im Repo schon umgesetzt ist

- `mod/mathetrainer/classes/bloom/package_builder.php` – erzeugt das Paket (Konzept, Folien, Assets)
  im Folienschema von Bloom; deterministisch über `seed`
- `mod/mathetrainer/cli/bloom_package.php` – Paket als JSON erzeugen:
  ```
  php mod/mathetrainer/cli/bloom_package.php --topic='Brüche' --grade=6 --count=10 --style=mixed -o=brueche.json
  ```
- In der Moodle-Aktivität: Lehrkräfte können das Paket zur aktuellen Aktivität herunterladen
  (Thema, Klasse, AFB und Kompetenz werden übernommen)
- Tests: jede Folie lösbar, genau eine richtige Option, keine doppelten Optionen, Distraktoren mit
  Fehlererklärung, akzeptierte Schreibweisen korrekt

### Paketformat (`mathetrainer-bloom-package`, Version 1)

```json
{
  "format": "mathetrainer-bloom-package",
  "version": 1,
  "project": { "title": "…", "description": "…" },
  "concept": { "audience": {…}, "goals": [{ "id": "L1", "text": "…", "bloom": "anwenden" }], "keyPoints": […], "address": "du" },
  "slides": [ { "type": "info", … }, { "type": "mc", "options": [{ "text": "7/12", "correct": true, "feedback": "…" }], "mathetrainer": { "templateid": "rational_addition", "seed": 123 } }, …, { "type": "result", … } ],
  "assets": { "numberline-3": { "type": "image/svg+xml", "content": "<svg …>" } },
  "source": { "generator": "mod_mathetrainer", "templates": […], "seed": 42, "style": "mixed", "items": […] }
}
```

`slides` kann direkt an Bloom (`create_slides`) übergeben werden. Bilder: `image.media = "asset:<key>"`
wird beim Anlegen durch die Kennung des hochgeladenen Bildes ersetzt.

## Pilot (26.11.2026)

Über Weg A (Bloom-Schnittstelle) wurden in der Organisation „eLeDia GmbH“ zwei Projekte angelegt, beide im Status
Entwurf:

| Projekt | Folien | Inhalt |
|---|---|---|
| Brüche am Zahlenstrahl und vergleichen (Klasse 6) | 10 | 4 × Bruch am Zahlenstrahl (Eingabe, mit Grafik), 4 × Brüche vergleichen (MC) |
| Rechnen mit negativen Zahlen (Klasse 7) | 11 | je 3 × Zahlenstrahl, Rechnen mit rationalen Zahlen, Fehler finden (gemischt Eingabe/MC) |

Ergebnisse:

- **Bloom-Prüfung: 0 Fehler, 0 Hinweise** (nach den unten genannten Korrekturen).
- **Zusatzfelder bleiben erhalten**: `mathetrainer.templateid` und `seed` stehen nach dem Anlegen weiter an der
  Folie. Damit ist „Variante neu würfeln“ technisch möglich (offene Frage 1 beantwortet).
- **SVG-Grafiken** nimmt Bloom direkt an (offene Frage 6 beantwortet). `currentColor` wurde durch eine feste Farbe
  ersetzt, damit die Grafik in jedem Bloom-Design gleich aussieht.
- **Alternativtexte** der Zahlenstrahle beschreiben Einteilung, Beschriftung und Lage des Punkts, damit die Aufgabe
  auch mit Screenreader lösbar ist.
- Bloom verlangt eine Kennung (`name`) für jede Eingabefolie und prüft, ob alle Lernziele und Kernaussagen von
  Folien abgedeckt werden. Beides erzeugt der Paket-Generator jetzt automatisch.

Noch zu prüfen im Pilot: Darstellung im Bloom-Player, SCORM-Export in einen Moodle-Kurs, Rückmeldung von
Lehrkräften zum Anpassen im Editor, Vergleich der Eingabe (z. B. ob „ 2/3“ mit Leerzeichen akzeptiert wird).

## Offene Fragen und Entscheidungen

1. **Statisch vs. dynamisch.** Ein Bloom-Paket hat feste Zahlen. Das ist für gestaltete Lerneinheiten
   richtig; für „unendlich üben“ bleibt die Mathetrainer-Aktivität. Soll Bloom eine Folie „neu würfeln“
   können? Dafür trägt jede Folie `mathetrainer.templateid` und `seed`; der Pilot zeigt, dass Bloom diese
   Felder erhält.
2. **Rückfluss der Fehlerdiagnosen.** Über SCORM kommt in Moodle nur die Gesamtnote sicher an. Damit
   der Mathetrainer-Bericht auch Bloom-Pakete auswertet, müsste Bloom je Frage die gewählte Option in
   die SCORM-Interaktionen schreiben (`cmi.interactions`). Der Mathetrainer könnte sie dann auslesen.
   → Frage an Bloom: Was landet heute in den SCORM-Interaktionen?
3. **Moodle-XML-Export als Alternative.** Bloom exportiert MC-Folien als Moodle-Fragen. Damit landen
   die Aufgaben samt Fehler-Feedback in der Fragensammlung und können in Moodle-Tests genutzt werden.
   Zu prüfen ist, ob das Feedback pro Option dabei erhalten bleibt.
4. **Datenschutz.** Zu Bloom fließen nur Aufgaben und Texte, **keine Schülerdaten**. Ergebnisse bleiben
   in Moodle. Das passt zu den Datenschutz-Grundsätzen des Projekts.
5. **Tarif und Rechte.** Im Free-Tarif von Bloom sind höchstens 3 eigene Projekte möglich; Pakete
   entstehen in der Organisation der Lehrkraft (Schreibrecht nötig).
6. **Zahlenstrahl-Bilder.** Bloom nimmt SVG direkt an (im Pilot geprüft).

## Nächste Schritte

1. Pilot fortsetzen: die beiden angelegten Pakete von einer Lehrkraft anpassen lassen und als SCORM in
   Moodle testen.
2. Mit dem Bloom-Team klären: Zusatzfelder an Folien, SCORM-Interaktionen, API für Weg C.
3. Danach: Button „In Bloom öffnen“ im Mathetrainer (Weg C) und Auswertung der Bloom-Ergebnisse im
   Mathetrainer-Bericht.
