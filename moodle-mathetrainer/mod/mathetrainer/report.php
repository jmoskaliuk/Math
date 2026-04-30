<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('mathetrainer', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/mathetrainer:addinstance', $context);

$PAGE->set_context($context);
$PAGE->set_url('/mod/mathetrainer/report.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('reporttitle', 'mathetrainer'));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reporttitle', 'mathetrainer'));

$mastery = $DB->get_records('mathetrainer_mastery', ['mathetrainerid' => $cm->instance]);
if (!$mastery) {
    echo $OUTPUT->notification(get_string('noreportdata', 'mathetrainer'), 'info');
    echo $OUTPUT->footer();
    exit;
}

$table = new html_table();
$table->head = ['User', 'Kompetenz', 'Mastery', 'Versuche'];
foreach ($mastery as $row) {
    $table->data[] = [
        (string)$row->userid,
        s($row->competencyid),
        round((float)$row->mastery * 100, 1) . '%',
        (int)$row->attemptcount,
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
