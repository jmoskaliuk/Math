<?php

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('mathetrainer', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$mathetrainer = $DB->get_record('mathetrainer', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_url('/mod/mathetrainer/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading(format_string($course->fullname));

$generator = new \mod_mathetrainer\generator\integer_numberline_generator();
$difficulty = (int)($mathetrainer->difficulty ?? 1);
$task = $generator->generate($USER->id + $cm->id, $difficulty);
$task['meta']['competencyid'] = (string)($mathetrainer->competencyid ?? 'GENERIC-COMP');

$sampleinput = optional_param('answer', '', PARAM_RAW_TRIMMED);
$feedbacktext = '';

if ($sampleinput !== '') {
    $attemptservice = new \mod_mathetrainer\service\attempt_service();
    $attemptid = $attemptservice->start_attempt((int)$cm->instance, (int)$USER->id);
    $itemid = $attemptservice->save_item($attemptid, $task);

    $validator = new \mod_mathetrainer\validator\numeric_validator();
    $validation = $validator->validate($task['expected'], ['value' => $sampleinput]);
    $attemptservice->save_response($itemid, (int)$USER->id, $validation, $sampleinput);
    $attemptservice->update_mastery((int)$cm->instance, (int)$USER->id, $task['meta']['competencyid'], (bool)$validation['iscorrect']);

    $explainer = new \mod_mathetrainer\explanation\basic_explainer();
    $feedbacktext = $explainer->explain($validation);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'mathetrainer'));
echo html_writer::tag('p', format_string($task['question']));

if ($feedbacktext !== '') {
    echo $OUTPUT->notification($feedbacktext, 'info');
}

echo html_writer::start_tag('form', ['method' => 'get']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'answer', 'value' => s($sampleinput)]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'value' => get_string('checkanswer', 'mathetrainer')]);
echo html_writer::end_tag('form');

if (has_capability('mod/mathetrainer:addinstance', $context)) {
    $url = new moodle_url('/mod/mathetrainer/report.php', ['id' => $cm->id]);
    echo html_writer::link($url, get_string('reporttitle', 'mathetrainer'));
}

echo $OUTPUT->footer();
