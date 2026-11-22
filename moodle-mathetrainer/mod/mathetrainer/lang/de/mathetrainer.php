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
