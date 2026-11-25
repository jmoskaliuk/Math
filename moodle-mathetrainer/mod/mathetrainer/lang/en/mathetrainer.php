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
$string['prompt_to_decimal'] = 'Write {$a} as a decimal.';
$string['prompt_to_fraction'] = 'Write {$a} as a fraction.';
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
$string['error_format_decimal'] = 'A decimal is asked for, e.g. 0.75.';
$string['error_format_fraction'] = 'A fraction is asked for, e.g. 3/4.';
$string['error_order_of_operations'] = 'You calculated from left to right. Multiplication and division come before addition and subtraction!';
$string['error_ignored_brackets'] = 'What is in brackets is calculated first.';
$string['error_digits_copied'] = 'Writing numerator and denominator next to each other does not give the decimal. Expand to 10, 100 or 1000.';
$string['error_place_value_error'] = 'Mind the place values: one decimal place means tenths, two mean hundredths.';

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
$string['errorname_format_decimal'] = 'Not in decimal notation';
$string['errorname_format_fraction'] = 'Not in fraction notation';
$string['errorname_order_of_operations'] = 'Order of operations ignored';
$string['errorname_ignored_brackets'] = 'Brackets ignored';
$string['errorname_digits_copied'] = 'Digits copied';
$string['errorname_place_value_error'] = 'Place value error';

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

// Grading.
$string['grademethod'] = 'Grading method';
$string['grademethod_help'] = 'How the gradebook grade is calculated from several rounds.';
$string['grademethod_highest'] = 'Highest round';
$string['grademethod_average'] = 'Average of all rounds';
$string['grademethod_first'] = 'First round';
$string['grademethod_last'] = 'Last round';

// School form.
$string['schoolform'] = 'School type';
$string['schoolform_help'] = 'Optional. Used to classify the activity and for reports; it does not affect which exercises are generated.';
$string['schoolform_gymnasium'] = 'Gymnasium';
$string['schoolform_realschule'] = 'Realschule';
$string['schoolform_gesamtschule'] = 'Gesamtschule';
$string['schoolform_sekundarschule'] = 'Sekundarschule';
$string['schoolform_gemeinschaftsschule'] = 'Gemeinschaftsschule';
$string['schoolform_hauptschule'] = 'Hauptschule';

// Exercise types.
$string['template_order_of_operations'] = 'Order of operations';
$string['template_decimal_convert'] = 'Fraction ↔ decimal';
$string['template_fraction_numberline'] = 'Fraction on the number line';
$string['template_fraction_compare'] = 'Comparing fractions';
$string['template_integer_numberline'] = 'Integer on the number line';
$string['template_rational_addition'] = 'Calculating with rational numbers';
$string['template_sign_error_analysis'] = 'Find the mistake';
$string['template_linear_equation'] = 'Linear equations';

// Report.
$string['report'] = 'Report';
$string['openreport'] = 'Open detailed report';
$string['reportsummary'] = '{$a->learners} learners, {$a->attempts} rounds, on average {$a->average} % correct.';
$string['classoverview'] = 'Class overview by exercise type';
$string['learneroverview'] = 'Learners';
$string['exercisetype'] = 'Exercise type';
$string['answers'] = 'Answers';
$string['successrate'] = 'Correct';
$string['aggregatedscore'] = 'Grade';
$string['recommendation'] = 'Recommendation';
$string['downloadreport'] = 'Download report';
$string['nolearners'] = 'There are no learners in this group.';
$string['recommendationrules'] = 'Recommendations: "repeat" from at least 3 answers with less than 60 % correct; "typical mistake" if one mistake occurs at least twice and in at least a third of the wrong answers; "secure" from 80 % correct with at least 5 answers.';
$string['rec_notstarted'] = 'Not started yet.';
$string['rec_mastered'] = 'Secure – can continue with more demanding exercises (AFB II/III).';
$string['rec_ontrack'] = 'On track.';
$string['rec_repeat'] = 'Repeat: {$a}.';
$string['rec_focus'] = 'Typical mistake: {$a}.';
$string['learnerrec_notstarted'] = 'Start your first round!';
$string['learnerrec_mastered'] = 'Great, you have got it! Try more difficult exercises.';
$string['learnerrec_ontrack'] = 'You are on a good track.';
$string['learnerrec_repeat'] = 'Practise again: {$a}.';
$string['learnerrec_focus'] = 'Pay special attention to: {$a}.';
// Bloom packages.
$string['mathetrainer:exportpackage'] = 'Create a Bloom learning package';
$string['bloompackage'] = 'Bloom learning package';
$string['bloompackage_intro'] = 'Creates a Bloom learning package from the exercise types of this activity: introduction with key points, exercises with feedback on typical mistakes and a result slide. You can adapt the package in Bloom and add it back to Moodle as SCORM.';
$string['exercisetypes'] = 'Exercise types';
$string['bloomcount'] = 'Number of exercises';
$string['bloomstyle'] = 'Exercise format';
$string['bloomstyle_mixed'] = 'Mixed: input and multiple choice';
$string['bloomstyle_mc'] = 'Multiple choice with typical mistakes as options';
$string['bloomstyle_input'] = 'Input only';
$string['bloomseed'] = 'Variant (seed)';
$string['bloomseed_help'] = 'Leave empty for new numbers. Same number = same package; e.g. use another number for parallel classes.';
$string['bloomdownload'] = 'Download package (JSON)';
$string['bloomprivacy'] = 'The package only contains exercises and explanations, no learner data.';
$string['bloom_intro_title'] = 'Let\'s go';
$string['bloom_intro_body'] = 'In this package you practise the following exercises. Read the key points and then work step by step.';
$string['bloom_intro_tip'] = 'If you make a mistake, read the feedback carefully – it explains where the mistake usually is.';
$string['bloom_result_title'] = 'Your result';
$string['bloom_result_pass'] = 'Great! You solved the exercises confidently.';
$string['bloom_result_fail'] = 'Not quite yet. Read the feedback and try again.';
$string['bloom_description'] = 'Practice package with {$a} exercises from the maths trainer.';
$string['bloom_audience'] = 'Lower secondary students.';
$string['bloom_audience_grade'] = 'Students in grade {$a}.';
$string['bloom_competency_note'] = 'Curriculum reference: {$a}. Created with the maths trainer.';
$string['keypoint_order_of_operations'] = 'Brackets first, then multiplication and division before addition and subtraction, otherwise left to right.';
$string['keypoint_decimal_convert'] = 'Expand the fraction to 10, 100 or 1000 – then you can read off the decimal.';
$string['keypoint_fraction_numberline'] = 'The denominator tells you into how many equal parts the piece from 0 to 1 is divided.';
$string['keypoint_fraction_compare'] = 'Same numerator: larger denominator, smaller fraction. Otherwise use a common denominator.';
$string['keypoint_integer_numberline'] = 'First find out what one tick stands for. Negative numbers are left of 0.';
$string['keypoint_rational_addition'] = 'Subtracting a negative number means adding its absolute value. With different signs, subtract the absolute values.';
$string['keypoint_sign_error_analysis'] = 'Check the sign first, then the absolute value.';
$string['keypoint_linear_equation'] = 'Do the same on both sides using the inverse operation: + becomes −, · becomes :.';
$string['goal_order_of_operations'] = 'Calculate terms with natural numbers using brackets and the order of operations.';
$string['goal_decimal_convert'] = 'Convert between fractions and decimals.';
$string['goal_fraction_numberline'] = 'Read off fractions on the number line.';
$string['goal_fraction_compare'] = 'Compare fractions by size.';
$string['goal_integer_numberline'] = 'Read off integers on the number line and work out the scale.';
$string['goal_rational_addition'] = 'Add and subtract rational numbers.';
$string['goal_sign_error_analysis'] = 'Find and explain mistakes in calculations with negative numbers.';
$string['goal_linear_equation'] = 'Solve linear equations by equivalent transformations.';
$string['bloompackagelink'] = 'Create Bloom learning package';
