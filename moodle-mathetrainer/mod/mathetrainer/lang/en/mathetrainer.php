<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * English language strings for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Maths trainer';
$string['modulename'] = 'Maths trainer';
$string['modulenameplural'] = 'Maths trainers';
$string['modulename_help'] = 'The maths trainer generates exercises for curriculum competencies, checks answers automatically and gives feedback on typical mistakes.';
$string['pluginadministration'] = 'Maths trainer administration';
$string['noinstances'] = 'There are no maths trainer activities in this course.';

// Capabilities.
$string['mathetrainer:addinstance'] = 'Add a maths trainer';
$string['mathetrainer:view'] = 'View maths trainer';
$string['mathetrainer:attempt'] = 'Work on maths trainer exercises';
$string['mathetrainer:viewreports'] = 'View maths trainer reports';

// Settings form.
$string['curriculumsettings'] = 'Curriculum and exercises';
$string['state'] = 'Federal state';
$string['state_nrw'] = 'North Rhine-Westphalia';
$string['state_bw'] = 'Baden-Württemberg';
$string['gradelevel'] = 'Grade';
$string['gradelevelvalue'] = 'Grade {$a}';
$string['topic'] = 'Topic';
$string['topic_help'] = 'Determines which exercise types are generated. If the topic has no exercises for the selected grade, all exercises of the topic are used.';
$string['alltopics'] = 'All topics';
$string['requirementlevel'] = 'Requirement level';
$string['any'] = 'Any';
$string['nocompetency'] = 'None (select by topic and grade)';
$string['competencyid'] = 'Competency ID';
$string['competencyid_help'] = 'Optional: curriculum competency (e.g. NRW-MATH-07-RATIONAL-ZAHLEN). If the curriculum pack links exercise types to it, exactly those are used. Mastery is tracked for this competency. If empty, exercises are selected by topic, grade and requirement level.';

// Learner view.
$string['startintro'] = 'Each round gives you {$a} new exercises. After submitting you immediately see what was right and get hints on your mistakes.';
$string['startattempt'] = 'Start exercises';
$string['startnewattempt'] = 'New exercises';
$string['attempt'] = 'Round';
$string['attemptno'] = 'Round {$a}';
$string['exerciseno'] = 'Exercise {$a}';
$string['prompt_calculate'] = 'Calculate {$a}';
$string['prompt_compare'] = 'Insert the correct sign (<, = or >): {$a}';
$string['prompt_numberline'] = 'Which number is marked on the number line?';
$string['prompt_fraction_numberline'] = 'Which fraction is marked on the number line?';
$string['prompt_find_error'] = 'This calculation is wrong: {$a}. What is the correct result?';
$string['prompt_equation'] = 'Solve the equation {$a}. Enter x.';
$string['numberline_alt'] = 'Number line with a marked point';
$string['numericinputhelp'] = 'Integer, fraction (e.g. -3/4) or decimal (e.g. 0.75)';
$string['submitanswers'] = 'Submit answers';
$string['resultheading'] = 'Result of round {$a}';
$string['resultsummary'] = '{$a->correct} of {$a->total} exercises correct.';
$string['youranswer'] = 'Your answer: {$a}';
$string['correctansweris'] = 'Correct answer: {$a}';
$string['yourattempts'] = 'Your previous rounds';
$string['score'] = 'Score';
$string['attemptalreadyfinished'] = 'This round has already been submitted.';
$string['noattemptcapability'] = 'You can view this activity but cannot work on exercises.';

// Feedback.
$string['feedback_correct'] = 'Correct!';
$string['hint_notreduced'] = 'Tip: you can still simplify the result.';
$string['error_empty'] = 'You did not enter an answer.';
$string['error_format'] = 'This is not a number. Write e.g. -7, 3/4 or 0.5.';
$string['error_wrong'] = 'Unfortunately wrong.';
$string['error_sign_error'] = 'The absolute value is right, but the sign is wrong.';
$string['error_double_negative'] = 'Careful: subtracting a negative number means adding its absolute value.';
$string['error_magnitude_error'] = 'With different signs, the absolute values are subtracted, not added.';
$string['error_added_denominators'] = 'You added numerators and denominators separately. Find a common denominator first.';
$string['error_denominator_misconception'] = 'A larger denominator means smaller parts: 1/3 is less than 1/2.';
$string['error_numerator_only'] = 'With different denominators, comparing the numerators is not enough. Use a common denominator.';
$string['error_relation_reversed'] = 'The sign is exactly the wrong way round.';
$string['error_not_equal'] = 'The fractions are not equal. Use a common denominator and compare.';
$string['error_equivalent_fractions'] = 'The fractions are equal – one is an expanded form of the other.';
$string['error_copied_wrong_result'] = 'That is exactly the wrong result from the exercise. Work it out yourself!';
$string['error_scale_error'] = 'You counted the ticks. Check what distance one tick stands for on this number line.';
$string['error_counted_marks'] = 'You counted the ticks instead of the parts. The denominator is the number of equal parts between 0 and 1.';
$string['error_unit_error'] = 'You took the whole number line as one whole. The whole is the part from 0 to 1.';
$string['error_inverse_operation'] = 'When rearranging, use the inverse operation: + becomes −, − becomes +.';
$string['error_forgot_divide'] = 'Almost: you still have to divide by the number in front of x.';
$string['error_multiplied_instead'] = 'The number in front of x is removed by dividing, not by multiplying.';

// Teacher report.
$string['reportheading'] = 'Report';
$string['noattemptsyet'] = 'No submitted rounds yet.';
$string['attempts'] = 'Rounds';
$string['averagescore'] = 'Average';
$string['lastattempt'] = 'Last round';
$string['commonerrors'] = 'Common mistakes';
$string['errorname_empty'] = 'No answer';
$string['errorname_format'] = 'Invalid input';
$string['errorname_wrong'] = 'Other mistake';
$string['errorname_sign_error'] = 'Sign error';
$string['errorname_double_negative'] = 'Minus before a negative number';
$string['errorname_magnitude_error'] = 'Absolute values added instead of subtracted';
$string['errorname_added_denominators'] = 'Denominators added';
$string['errorname_denominator_misconception'] = 'Larger denominator = larger fraction';
$string['errorname_numerator_only'] = 'Only numerators compared';
$string['errorname_relation_reversed'] = 'Sign reversed';
$string['errorname_not_equal'] = 'Wrongly equal';
$string['errorname_equivalent_fractions'] = 'Equivalent fractions not recognised';
$string['errorname_copied_wrong_result'] = 'Wrong result copied';
$string['errorname_scale_error'] = 'Scale ignored';
$string['errorname_counted_marks'] = 'Counted ticks instead of parts';
$string['errorname_unit_error'] = 'Wrong whole';
$string['errorname_inverse_operation'] = 'Wrong inverse operation';
$string['errorname_forgot_divide'] = 'Forgot to divide';
$string['errorname_multiplied_instead'] = 'Multiplied instead of divided';

// Course reset.
$string['resetattempts'] = 'Delete all rounds and mastery data';

// Privacy.
$string['privacy:metadata:attempts'] = 'Learner rounds.';
$string['privacy:metadata:attempts:userid'] = 'The user who worked on the round.';
$string['privacy:metadata:attempts:attemptno'] = 'Round number.';
$string['privacy:metadata:attempts:status'] = 'Status of the round.';
$string['privacy:metadata:attempts:score'] = 'Share of correct answers.';
$string['privacy:metadata:attempts:timecreated'] = 'Start of the round.';
$string['privacy:metadata:attempts:timemodified'] = 'Last modification of the round.';
$string['privacy:metadata:responses'] = 'Answers to individual exercises.';
$string['privacy:metadata:responses:userid'] = 'The user who answered.';
$string['privacy:metadata:responses:responsejson'] = 'The entered answer and the check result.';
$string['privacy:metadata:responses:iscorrect'] = 'Whether the answer was correct.';
$string['privacy:metadata:responses:feedback'] = 'Detected error category.';
$string['privacy:metadata:responses:timesubmitted'] = 'Time of submission.';
$string['privacy:metadata:mastery'] = 'Mastery per competency.';
$string['privacy:metadata:mastery:userid'] = 'The user whose mastery is tracked.';
$string['privacy:metadata:mastery:competencyid'] = 'The competency or topic.';
$string['privacy:metadata:mastery:mastery'] = 'Average share of correct answers.';
$string['privacy:metadata:mastery:attemptcount'] = 'Number of rounds taken into account.';
$string['privacy:metadata:mastery:timemodified'] = 'Last update.';
