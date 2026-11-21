<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Main page: learners work on generated exercises, teachers see an overview.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/user/lib.php');

use mod_mathetrainer\local\attempt_manager;

$id = required_param('id', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$attemptid = optional_param('attempt', 0, PARAM_INT);

$cm = get_coursemodule_from_id('mathetrainer', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$instance = $DB->get_record('mathetrainer', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/mathetrainer:view', $context);

$pageurl = new moodle_url('/mod/mathetrainer/view.php', ['id' => $cm->id]);
$PAGE->set_url($pageurl);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($instance);

$manager = new attempt_manager($DB, $instance);
$canattempt = has_capability('mod/mathetrainer:attempt', $context) && !isguestuser();

// Actions (POST only, with sesskey).
if ($action !== '' && $canattempt) {
    require_sesskey();
    if ($action === 'start') {
        $manager->start_attempt((int)$USER->id);
        redirect($pageurl);
    }
    if ($action === 'submit') {
        $attempt = $manager->get_inprogress_attempt((int)$USER->id);
        if ($attempt && (int)$attempt->id === $attemptid) {
            $answers = [];
            foreach (array_keys($manager->get_items((int)$attempt->id)) as $slot) {
                $answers[$slot] = optional_param('answer_' . $slot, '', PARAM_RAW_TRIMMED);
            }
            $manager->submit($attempt, $answers);
            redirect(new moodle_url($pageurl, ['attempt' => $attempt->id]));
        }
        redirect($pageurl);
    }
}

\mod_mathetrainer\event\course_module_viewed::create([
    'objectid' => $instance->id,
    'context' => $context,
])->trigger();
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

/**
 * Renders the prompt of an item.
 */
$renderprompt = static function (stdClass $item): string {
    $payload = $item->payload;
    $key = (string)($payload['promptkey'] ?? 'prompt_calculate');
    return get_string($key, 'mathetrainer', s((string)($payload['expression'] ?? '')));
};

/**
 * Readable feedback for a validation result.
 */
$renderfeedback = static function (array $result, array $expected): string {
    if (!empty($result['correct'])) {
        $text = get_string('feedback_correct', 'mathetrainer');
        if (($result['hint'] ?? null) === 'notreduced') {
            $text .= ' ' . get_string('hint_notreduced', 'mathetrainer');
        }
        return $text;
    }
    $category = (string)($result['category'] ?? 'wrong');
    $key = 'error_' . $category;
    $text = get_string_manager()->string_exists($key, 'mathetrainer')
        ? get_string($key, 'mathetrainer')
        : get_string('error_wrong', 'mathetrainer');
    $answer = str_replace('-', "\u{2212}", (string)$expected['answer']);
    return $text . ' ' . get_string('correctansweris', 'mathetrainer', s($answer));
};

echo $OUTPUT->header();

if (!empty($instance->intro)) {
    echo $OUTPUT->box(format_module_intro('mathetrainer', $instance, $cm->id), 'generalbox', 'intro');
}

if ($canattempt) {
    $inprogress = $manager->get_inprogress_attempt((int)$USER->id);
    $review = $attemptid ? $manager->get_attempt($attemptid, (int)$USER->id) : null;

    if ($inprogress) {
        // Exercise form.
        echo $OUTPUT->heading(get_string('attemptno', 'mathetrainer', $inprogress->attemptno), 3);
        echo html_writer::start_tag('form', ['method' => 'post', 'action' => $pageurl->out(false)]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'submit']);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'attempt', 'value' => $inprogress->id]);

        foreach ($manager->get_items((int)$inprogress->id) as $slot => $item) {
            $fieldid = 'mathetrainer_answer_' . $slot;
            echo html_writer::start_div('card mb-3');
            echo html_writer::start_div('card-body');
            echo html_writer::tag('label', get_string('exerciseno', 'mathetrainer', $slot) . ': ' . $renderprompt($item),
                ['for' => $fieldid, 'class' => 'd-block font-weight-bold mb-2']);

            if (($item->payload['type'] ?? '') === 'choice') {
                $options = ['' => get_string('choose')];
                foreach ((array)$item->payload['options'] as $option) {
                    $options[$option] = $option;
                }
                echo html_writer::select($options, 'answer_' . $slot, '', false, ['id' => $fieldid, 'class' => 'custom-select']);
            } else {
                echo html_writer::empty_tag('input', [
                    'type' => 'text',
                    'name' => 'answer_' . $slot,
                    'id' => $fieldid,
                    'class' => 'form-control',
                    'autocomplete' => 'off',
                    'inputmode' => 'text',
                    'style' => 'max-width: 12rem',
                ]);
                echo html_writer::tag('small', get_string('numericinputhelp', 'mathetrainer'), ['class' => 'form-text text-muted']);
            }
            echo html_writer::end_div();
            echo html_writer::end_div();
        }

        echo html_writer::empty_tag('input', [
            'type' => 'submit',
            'class' => 'btn btn-primary',
            'value' => get_string('submitanswers', 'mathetrainer'),
        ]);
        echo html_writer::end_tag('form');

    } else {
        if ($review && $review->status === attempt_manager::STATUS_FINISHED) {
            // Feedback for the attempt just finished (or selected).
            $items = $manager->get_items((int)$review->id);
            $responses = $manager->get_responses((int)$review->id);
            $correct = count(array_filter($responses, static fn($r) => (int)$r->iscorrect === 1));

            echo $OUTPUT->heading(get_string('resultheading', 'mathetrainer', $review->attemptno), 3);
            echo $OUTPUT->notification(
                get_string('resultsummary', 'mathetrainer', (object)['correct' => $correct, 'total' => count($items)]),
                $correct === count($items) ? 'success' : 'info'
            );

            foreach ($items as $slot => $item) {
                $response = $responses[$slot] ?? null;
                $result = $response ? (array)$response->result : ['correct' => false, 'category' => 'empty'];
                $given = $response ? (string)($result['answer'] ?? '') : '';
                $class = !empty($result['correct']) ? 'border-success' : 'border-danger';

                echo html_writer::start_div("card mb-3 $class");
                echo html_writer::start_div('card-body');
                echo html_writer::div(get_string('exerciseno', 'mathetrainer', $slot) . ': ' . $renderprompt($item),
                    'font-weight-bold');
                echo html_writer::div(get_string('youranswer', 'mathetrainer', $given === '' ? '–' : s($given)));
                echo html_writer::div($renderfeedback($result, $item->expected), 'mt-1');
                echo html_writer::end_div();
                echo html_writer::end_div();
            }
        } else {
            echo $OUTPUT->paragraph(get_string('startintro', 'mathetrainer', attempt_manager::ITEMS_PER_ATTEMPT));
        }

        echo $OUTPUT->single_button(
            new moodle_url($pageurl, ['action' => 'start']),
            get_string($review ? 'startnewattempt' : 'startattempt', 'mathetrainer')
        );

        $finished = $manager->get_finished_attempts((int)$USER->id);
        if ($finished) {
            echo $OUTPUT->heading(get_string('yourattempts', 'mathetrainer'), 4, 'mt-4');
            $table = new html_table();
            $table->head = [get_string('attempt', 'mathetrainer'), get_string('score', 'mathetrainer'), get_string('date')];
            foreach ($finished as $attempt) {
                $table->data[] = [
                    html_writer::link(new moodle_url($pageurl, ['attempt' => $attempt->id]), (int)$attempt->attemptno),
                    format_float((float)$attempt->score * 100, 0) . ' %',
                    userdate((int)$attempt->timemodified),
                ];
            }
            echo html_writer::table($table);
        }
    }
}

if (has_capability('mod/mathetrainer:viewreports', $context)) {
    echo $OUTPUT->heading(get_string('reportheading', 'mathetrainer'), 3, 'mt-4');
    $rows = $manager->get_report_rows();
    if (!$rows) {
        echo $OUTPUT->notification(get_string('noattemptsyet', 'mathetrainer'), 'info');
    } else {
        $users = user_get_users_by_id(array_keys($rows));
        $table = new html_table();
        $table->head = [
            get_string('fullname'),
            get_string('attempts', 'mathetrainer'),
            get_string('averagescore', 'mathetrainer'),
            get_string('lastattempt', 'mathetrainer'),
        ];
        foreach ($rows as $userid => $row) {
            $table->data[] = [
                isset($users[$userid]) ? fullname($users[$userid]) : '#' . (int)$userid,
                (int)$row->attempts,
                format_float((float)$row->avgscore * 100, 0) . ' %',
                userdate((int)$row->lastattempt),
            ];
        }
        echo html_writer::table($table);

        $errors = $manager->get_error_statistics();
        if ($errors) {
            echo $OUTPUT->heading(get_string('commonerrors', 'mathetrainer'), 4);
            $list = [];
            $strings = get_string_manager();
            foreach ($errors as $category => $count) {
                $key = 'errorname_' . $category;
                $label = $strings->string_exists($key, 'mathetrainer') ? get_string($key, 'mathetrainer') : s($category);
                $list[] = $label . ': ' . $count;
            }
            echo html_writer::alist($list);
        }
    }
}

if (!$canattempt && !has_capability('mod/mathetrainer:viewreports', $context)) {
    echo $OUTPUT->notification(get_string('noattemptcapability', 'mathetrainer'), 'info');
}

echo $OUTPUT->footer();
