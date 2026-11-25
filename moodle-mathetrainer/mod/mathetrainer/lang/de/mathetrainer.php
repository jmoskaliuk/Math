<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * German language strings for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Mathetrainer';
$string['modulename'] = 'Mathetrainer';
$string['modulenameplural'] = 'Mathetrainer';
$string['modulename_help'] = 'Der Mathetrainer erzeugt passende Übungsaufgaben zu Lehrplan-Kompetenzen, prüft die Antworten automatisch und gibt Rückmeldung zu typischen Fehlern.';
$string['pluginadministration'] = 'Mathetrainer-Administration';
$string['noinstances'] = 'In diesem Kurs gibt es keine Mathetrainer-Aktivitäten.';

// Capabilities.
$string['mathetrainer:addinstance'] = 'Mathetrainer hinzufügen';
$string['mathetrainer:view'] = 'Mathetrainer ansehen';
$string['mathetrainer:attempt'] = 'Aufgaben im Mathetrainer bearbeiten';
$string['mathetrainer:viewreports'] = 'Auswertungen im Mathetrainer ansehen';

// Settings form.
$string['curriculumsettings'] = 'Lehrplan und Aufgaben';
$string['state'] = 'Bundesland';
$string['state_nrw'] = 'Nordrhein-Westfalen';
$string['state_bw'] = 'Baden-Württemberg';
$string['gradelevel'] = 'Klassenstufe';
$string['gradelevelvalue'] = 'Klasse {$a}';
$string['topic'] = 'Thema';
$string['topic_help'] = 'Bestimmt, welche Aufgabentypen erzeugt werden. Gibt es zum Thema keine Aufgaben für die gewählte Klassenstufe, werden alle Aufgaben des Themas verwendet.';
$string['alltopics'] = 'Alle Themen';
$string['requirementlevel'] = 'Anforderungsbereich';
$string['any'] = 'Beliebig';
$string['nocompetency'] = 'Keine (nach Thema und Klasse auswählen)';
$string['competencyid'] = 'Kompetenz-ID';
$string['competencyid_help'] = 'Optional: Kompetenz aus dem Curriculum (z. B. NRW-MATH-07-RATIONAL-ZAHLEN). Sind ihr im Curriculum-Pack Aufgabentypen zugeordnet, werden genau diese verwendet. Der Lernstand wird für diese Kompetenz erfasst. Ohne Angabe wird nach Thema, Klasse und AFB ausgewählt.';

// Learner view.
$string['startintro'] = 'In jedem Durchgang bekommst du {$a} neue Aufgaben. Nach dem Abgeben siehst du sofort, was richtig war, und Hinweise zu deinen Fehlern.';
$string['startattempt'] = 'Aufgaben starten';
$string['startnewattempt'] = 'Neue Aufgaben';
$string['attempt'] = 'Durchgang';
$string['attemptno'] = 'Durchgang {$a}';
$string['exerciseno'] = 'Aufgabe {$a}';
$string['prompt_calculate'] = 'Berechne {$a}';
$string['prompt_compare'] = 'Setze das richtige Zeichen ein (<, = oder >): {$a}';
$string['prompt_numberline'] = 'Welche Zahl ist am Zahlenstrahl markiert?';
$string['prompt_fraction_numberline'] = 'Welcher Bruch ist am Zahlenstrahl markiert?';
$string['prompt_find_error'] = 'Diese Rechnung ist falsch: {$a}. Wie lautet das richtige Ergebnis?';
$string['prompt_equation'] = 'Löse die Gleichung {$a}. Gib x an.';
$string['prompt_to_decimal'] = 'Schreibe {$a} als Dezimalzahl.';
$string['prompt_to_fraction'] = 'Schreibe {$a} als Bruch.';
$string['numberline_alt'] = 'Zahlenstrahl mit einem markierten Punkt';
$string['numericinputhelp'] = 'Ganze Zahl, Bruch (z. B. -3/4) oder Dezimalzahl (z. B. 0,75)';
$string['submitanswers'] = 'Antworten abgeben';
$string['resultheading'] = 'Ergebnis von Durchgang {$a}';
$string['resultsummary'] = '{$a->correct} von {$a->total} Aufgaben richtig.';
$string['youranswer'] = 'Deine Antwort: {$a}';
$string['correctansweris'] = 'Richtig ist: {$a}';
$string['yourattempts'] = 'Deine bisherigen Durchgänge';
$string['score'] = 'Ergebnis';
$string['attemptalreadyfinished'] = 'Dieser Durchgang wurde bereits abgegeben.';
$string['noattemptcapability'] = 'Du kannst diese Aktivität ansehen, aber keine Aufgaben bearbeiten.';

// Feedback.
$string['feedback_correct'] = 'Richtig!';
$string['hint_notreduced'] = 'Tipp: Du kannst das Ergebnis noch kürzen.';
$string['error_empty'] = 'Du hast keine Antwort eingegeben.';
$string['error_format'] = 'Diese Eingabe ist keine Zahl. Schreibe z. B. -7, 3/4 oder 0,5.';
$string['error_wrong'] = 'Leider falsch.';
$string['error_sign_error'] = 'Der Betrag stimmt, aber das Vorzeichen ist falsch.';
$string['error_double_negative'] = 'Achtung: Eine negative Zahl abziehen heißt, ihren Betrag zu addieren – aus − (−3) wird + 3.';
$string['error_magnitude_error'] = 'Bei unterschiedlichen Vorzeichen werden die Beträge subtrahiert, nicht addiert.';
$string['error_added_denominators'] = 'Du hast Zähler und Nenner einzeln addiert. Bringe die Brüche zuerst auf einen gemeinsamen Nenner.';
$string['error_denominator_misconception'] = 'Ein größerer Nenner bedeutet kleinere Teile: 1/3 ist kleiner als 1/2.';
$string['error_numerator_only'] = 'Bei unterschiedlichen Nennern reicht es nicht, nur die Zähler zu vergleichen. Erweitere auf einen gemeinsamen Nenner.';
$string['error_relation_reversed'] = 'Das Zeichen ist genau umgekehrt.';
$string['error_not_equal'] = 'Die Brüche sind nicht gleich groß. Erweitere auf einen gemeinsamen Nenner und vergleiche.';
$string['error_equivalent_fractions'] = 'Die Brüche sind gleich groß – einer ist eine erweiterte Form des anderen.';
$string['error_copied_wrong_result'] = 'Das ist genau das falsche Ergebnis aus der Aufgabe. Rechne selbst nach!';
$string['error_scale_error'] = 'Du hast die Striche gezählt. Achte darauf, welchen Abstand ein Strich auf diesem Zahlenstrahl bedeutet.';
$string['error_counted_marks'] = 'Du hast die Striche gezählt statt der Abschnitte. Der Nenner ist die Zahl der gleich großen Teile zwischen 0 und 1.';
$string['error_unit_error'] = 'Du hast den ganzen Zahlenstrahl als ein Ganzes genommen. Das Ganze ist der Abschnitt von 0 bis 1.';
$string['error_inverse_operation'] = 'Beim Umformen muss die Zahl mit der Gegenrechnung auf die andere Seite: aus + wird −, aus − wird +.';
$string['error_forgot_divide'] = 'Fast: Du musst am Ende noch durch die Zahl vor dem x teilen.';
$string['error_multiplied_instead'] = 'Die Zahl vor dem x wird durch Teilen entfernt, nicht durch Malnehmen.';
$string['error_format_decimal'] = 'Gefragt ist eine Dezimalzahl mit Komma, z. B. 0,75.';
$string['error_format_fraction'] = 'Gefragt ist ein Bruch mit Bruchstrich, z. B. 3/4.';
$string['error_order_of_operations'] = 'Du hast von links nach rechts gerechnet. Es gilt: Punkt vor Strich!';
$string['error_ignored_brackets'] = 'Was in der Klammer steht, wird zuerst gerechnet.';
$string['error_digits_copied'] = 'Zähler und Nenner hintereinander zu schreiben ergibt nicht die Dezimalzahl. Erweitere auf 10, 100 oder 1000.';
$string['error_place_value_error'] = 'Achte auf die Stellenwerte: Eine Stelle nach dem Komma sind Zehntel, zwei Stellen Hundertstel.';

// Teacher report.
$string['reportheading'] = 'Auswertung';
$string['noattemptsyet'] = 'Noch keine abgegebenen Durchgänge.';
$string['attempts'] = 'Durchgänge';
$string['averagescore'] = 'Durchschnitt';
$string['lastattempt'] = 'Letzter Durchgang';
$string['commonerrors'] = 'Häufige Fehler';
$string['errorname_empty'] = 'Keine Antwort';
$string['errorname_format'] = 'Ungültige Eingabe';
$string['errorname_wrong'] = 'Sonstiger Fehler';
$string['errorname_sign_error'] = 'Vorzeichenfehler';
$string['errorname_double_negative'] = 'Minus vor negativer Zahl';
$string['errorname_magnitude_error'] = 'Beträge addiert statt subtrahiert';
$string['errorname_added_denominators'] = 'Nenner addiert';
$string['errorname_denominator_misconception'] = 'Größerer Nenner = größerer Bruch';
$string['errorname_numerator_only'] = 'Nur Zähler verglichen';
$string['errorname_relation_reversed'] = 'Zeichen vertauscht';
$string['errorname_not_equal'] = 'Fälschlich gleich';
$string['errorname_equivalent_fractions'] = 'Gleichwertige Brüche nicht erkannt';
$string['errorname_copied_wrong_result'] = 'Falsches Ergebnis übernommen';
$string['errorname_scale_error'] = 'Skala nicht beachtet';
$string['errorname_counted_marks'] = 'Striche statt Abschnitte gezählt';
$string['errorname_unit_error'] = 'Falsches Ganzes';
$string['errorname_inverse_operation'] = 'Falsche Gegenrechnung';
$string['errorname_forgot_divide'] = 'Teilen vergessen';
$string['errorname_multiplied_instead'] = 'Multipliziert statt geteilt';
$string['errorname_format_decimal'] = 'Keine Dezimalschreibweise';
$string['errorname_format_fraction'] = 'Keine Bruchschreibweise';
$string['errorname_order_of_operations'] = 'Punkt vor Strich missachtet';
$string['errorname_ignored_brackets'] = 'Klammer missachtet';
$string['errorname_digits_copied'] = 'Ziffern abgeschrieben';
$string['errorname_place_value_error'] = 'Stellenwertfehler';

// Course reset.
$string['resetattempts'] = 'Alle Durchgänge und Lernstände löschen';

// Privacy.
$string['privacy:metadata:attempts'] = 'Durchgänge von Lernenden.';
$string['privacy:metadata:attempts:userid'] = 'Die Person, die den Durchgang bearbeitet hat.';
$string['privacy:metadata:attempts:attemptno'] = 'Nummer des Durchgangs.';
$string['privacy:metadata:attempts:status'] = 'Status des Durchgangs.';
$string['privacy:metadata:attempts:score'] = 'Anteil richtiger Antworten.';
$string['privacy:metadata:attempts:timecreated'] = 'Beginn des Durchgangs.';
$string['privacy:metadata:attempts:timemodified'] = 'Letzte Änderung des Durchgangs.';
$string['privacy:metadata:responses'] = 'Antworten auf einzelne Aufgaben.';
$string['privacy:metadata:responses:userid'] = 'Die Person, die geantwortet hat.';
$string['privacy:metadata:responses:responsejson'] = 'Die eingegebene Antwort und das Prüfergebnis.';
$string['privacy:metadata:responses:iscorrect'] = 'Ob die Antwort richtig war.';
$string['privacy:metadata:responses:feedback'] = 'Erkannte Fehlerkategorie.';
$string['privacy:metadata:responses:timesubmitted'] = 'Zeitpunkt der Abgabe.';
$string['privacy:metadata:mastery'] = 'Lernstand je Kompetenz.';
$string['privacy:metadata:mastery:userid'] = 'Die Person, deren Lernstand erfasst wird.';
$string['privacy:metadata:mastery:competencyid'] = 'Die Kompetenz bzw. das Thema.';
$string['privacy:metadata:mastery:mastery'] = 'Durchschnittlicher Anteil richtiger Antworten.';
$string['privacy:metadata:mastery:attemptcount'] = 'Anzahl der berücksichtigten Durchgänge.';
$string['privacy:metadata:mastery:timemodified'] = 'Letzte Aktualisierung.';

// Grading.
$string['grademethod'] = 'Bewertungsmethode';
$string['grademethod_help'] = 'Legt fest, wie aus mehreren Durchgängen die Note im Gradebook berechnet wird.';
$string['grademethod_highest'] = 'Bester Durchgang';
$string['grademethod_average'] = 'Durchschnitt aller Durchgänge';
$string['grademethod_first'] = 'Erster Durchgang';
$string['grademethod_last'] = 'Letzter Durchgang';

// School form.
$string['schoolform'] = 'Schulform';
$string['schoolform_help'] = 'Optional. Dient der Einordnung der Aktivität und der Auswertung; die Aufgabenauswahl hängt davon nicht ab.';
$string['schoolform_gymnasium'] = 'Gymnasium';
$string['schoolform_realschule'] = 'Realschule';
$string['schoolform_gesamtschule'] = 'Gesamtschule';
$string['schoolform_sekundarschule'] = 'Sekundarschule';
$string['schoolform_gemeinschaftsschule'] = 'Gemeinschaftsschule';
$string['schoolform_hauptschule'] = 'Hauptschule';

// Exercise types.
$string['template_order_of_operations'] = 'Rechenregeln';
$string['template_decimal_convert'] = 'Bruch ↔ Dezimalzahl';
$string['template_fraction_numberline'] = 'Bruch am Zahlenstrahl';
$string['template_fraction_compare'] = 'Brüche vergleichen';
$string['template_integer_numberline'] = 'Ganze Zahl am Zahlenstrahl';
$string['template_rational_addition'] = 'Rechnen mit rationalen Zahlen';
$string['template_sign_error_analysis'] = 'Fehler finden';
$string['template_linear_equation'] = 'Lineare Gleichungen';

// Report.
$string['report'] = 'Bericht';
$string['openreport'] = 'Ausführlichen Bericht öffnen';
$string['reportsummary'] = '{$a->learners} Lernende, {$a->attempts} Durchgänge, durchschnittlich {$a->average} % richtig.';
$string['classoverview'] = 'Klassenübersicht nach Aufgabentyp';
$string['learneroverview'] = 'Lernende';
$string['exercisetype'] = 'Aufgabentyp';
$string['answers'] = 'Antworten';
$string['successrate'] = 'Richtig';
$string['aggregatedscore'] = 'Bewertung';
$string['recommendation'] = 'Empfehlung';
$string['downloadreport'] = 'Bericht herunterladen';
$string['nolearners'] = 'In dieser Gruppe gibt es keine Lernenden.';
$string['recommendationrules'] = 'Empfehlungen: „Wiederholen“ ab mindestens 3 Antworten mit weniger als 60 % richtig; „Typischer Fehler“, wenn ein Fehler mindestens zweimal und bei mindestens einem Drittel der falschen Antworten auftritt; „Sicher“ ab 80 % richtig bei mindestens 5 Antworten.';
$string['rec_notstarted'] = 'Noch nicht begonnen.';
$string['rec_mastered'] = 'Sicher – kann mit anspruchsvolleren Aufgaben (AFB II/III) weitermachen.';
$string['rec_ontrack'] = 'Auf gutem Weg.';
$string['rec_repeat'] = 'Wiederholen: {$a}.';
$string['rec_focus'] = 'Typischer Fehler: {$a}.';
$string['learnerrec_notstarted'] = 'Starte deinen ersten Durchgang!';
$string['learnerrec_mastered'] = 'Super, das sitzt! Du kannst dich an schwierigere Aufgaben wagen.';
$string['learnerrec_ontrack'] = 'Du bist auf einem guten Weg.';
$string['learnerrec_repeat'] = 'Übe noch einmal: {$a}.';
$string['learnerrec_focus'] = 'Achte besonders auf: {$a}.';
// Bloom packages.
$string['mathetrainer:exportpackage'] = 'Bloom-Lernpaket erzeugen';
$string['bloompackage'] = 'Bloom-Lernpaket';
$string['bloompackage_intro'] = 'Erzeugt aus den Aufgabentypen dieser Aktivität ein Lernpaket für Bloom: Einstieg mit Merksätzen, Aufgaben mit Rückmeldung zu typischen Fehlern und eine Ergebnis-Folie. In Bloom kannst du das Paket anpassen und als SCORM wieder in Moodle einbinden.';
$string['exercisetypes'] = 'Aufgabentypen';
$string['bloomcount'] = 'Anzahl der Aufgaben';
$string['bloomstyle'] = 'Aufgabenform';
$string['bloomstyle_mixed'] = 'Gemischt: Eingabe und Multiple Choice';
$string['bloomstyle_mc'] = 'Multiple Choice mit typischen Fehlern als Antwortoptionen';
$string['bloomstyle_input'] = 'Nur Eingabe';
$string['bloomseed'] = 'Variante (Seed)';
$string['bloomseed_help'] = 'Leer lassen für neue Zahlen. Gleiche Zahl = gleiches Paket, z. B. für Parallelklassen eine andere Zahl wählen.';
$string['bloomdownload'] = 'Paket herunterladen (JSON)';
$string['bloomprivacy'] = 'Das Paket enthält nur Aufgaben und Erklärtexte, keine Daten von Lernenden.';
$string['bloom_intro_title'] = 'Los geht\'s';
$string['bloom_intro_body'] = 'In diesem Paket übst du die folgenden Aufgaben. Lies dir die Merksätze durch und rechne dann Schritt für Schritt.';
$string['bloom_intro_tip'] = 'Wenn du dich vertust, lies die Rückmeldung genau – sie erklärt, wo der Fehler meistens steckt.';
$string['bloom_result_title'] = 'Dein Ergebnis';
$string['bloom_result_pass'] = 'Stark! Du hast die Aufgaben sicher gelöst.';
$string['bloom_result_fail'] = 'Noch nicht ganz. Schau dir die Rückmeldungen an und versuche es noch einmal.';
$string['bloom_description'] = 'Übungspaket mit {$a} Aufgaben aus dem Mathetrainer.';
$string['bloom_audience'] = 'Schülerinnen und Schüler der Sekundarstufe I.';
$string['bloom_audience_grade'] = 'Schülerinnen und Schüler der Klasse {$a}.';
$string['bloom_competency_note'] = 'Lehrplanbezug: {$a}. Erzeugt mit dem Mathetrainer.';
$string['keypoint_order_of_operations'] = 'Klammern zuerst, dann Punkt vor Strich, sonst von links nach rechts.';
$string['keypoint_decimal_convert'] = 'Erweitere den Bruch auf 10, 100 oder 1000 – dann kannst du die Dezimalzahl direkt ablesen.';
$string['keypoint_fraction_numberline'] = 'Der Nenner sagt, in wie viele gleiche Teile das Stück von 0 bis 1 geteilt ist.';
$string['keypoint_fraction_compare'] = 'Gleicher Zähler: größerer Nenner, kleinerer Bruch. Sonst auf einen gemeinsamen Nenner bringen.';
$string['keypoint_integer_numberline'] = 'Finde zuerst heraus, wie viel ein Strich bedeutet. Links von 0 liegen die negativen Zahlen.';
$string['keypoint_rational_addition'] = 'Eine negative Zahl abziehen heißt, ihren Betrag zu addieren. Bei verschiedenen Vorzeichen werden die Beträge subtrahiert.';
$string['keypoint_sign_error_analysis'] = 'Prüfe zuerst das Vorzeichen, dann den Betrag.';
$string['keypoint_linear_equation'] = 'Rechne auf beiden Seiten das Gleiche – mit der Gegenrechnung: aus + wird −, aus · wird :.';
$string['goal_order_of_operations'] = 'Terme mit natürlichen Zahlen unter Beachtung von Klammern und Punkt vor Strich berechnen.';
$string['goal_decimal_convert'] = 'Zwischen Bruch- und Dezimalschreibweise wechseln.';
$string['goal_fraction_numberline'] = 'Brüche am Zahlenstrahl ablesen.';
$string['goal_fraction_compare'] = 'Brüche der Größe nach vergleichen.';
$string['goal_integer_numberline'] = 'Ganze Zahlen am Zahlenstrahl ablesen und die Skala erschließen.';
$string['goal_rational_addition'] = 'Rationale Zahlen addieren und subtrahieren.';
$string['goal_sign_error_analysis'] = 'Fehler in Rechnungen mit negativen Zahlen finden und begründen.';
$string['goal_linear_equation'] = 'Lineare Gleichungen durch Äquivalenzumformungen lösen.';
$string['bloompackagelink'] = 'Bloom-Lernpaket erzeugen';
