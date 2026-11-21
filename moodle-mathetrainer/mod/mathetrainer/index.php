<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Lists all Mathetrainer activities in a course.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$course = get_course($id);

require_course_login($course);

$context = context_course::instance($course->id);
\mod_mathetrainer\event\course_module_instance_list_viewed::create(['context' => $context])->trigger();

$PAGE->set_url('/mod/mathetrainer/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->shortname) . ': ' . get_string('modulenameplural', 'mathetrainer'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('modulenameplural', 'mathetrainer'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mathetrainer'));

$instances = get_all_instances_in_course('mathetrainer', $course);
if (!$instances) {
    notice(get_string('noinstances', 'mathetrainer'), new moodle_url('/course/view.php', ['id' => $course->id]));
}

$table = new html_table();
$table->head = [get_string('name'), get_string('topic', 'mathetrainer'), get_string('gradelevel', 'mathetrainer')];
foreach ($instances as $instance) {
    $link = html_writer::link(
        new moodle_url('/mod/mathetrainer/view.php', ['id' => $instance->coursemodule]),
        format_string($instance->name),
        $instance->visible ? [] : ['class' => 'dimmed']
    );
    $table->data[] = [
        $link,
        $instance->topic ? s($instance->topic) : get_string('alltopics', 'mathetrainer'),
        (int)$instance->gradelevel,
    ];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
