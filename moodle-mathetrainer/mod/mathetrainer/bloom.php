<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Teachers create a Bloom learning package from the exercise types of this activity.
 *
 * The package (JSON) contains only exercises and texts, no learner data.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_mathetrainer\bloom\package_builder;
use mod_mathetrainer\local\attempt_manager;

$id = required_param('id', PARAM_INT);
$count = optional_param('count', 10, PARAM_INT);
$style = optional_param('style', package_builder::STYLE_MIXED, PARAM_ALPHA);
$seed = optional_param('seed', 0, PARAM_INT);
$download = optional_param('download', 0, PARAM_BOOL);

$cm = get_coursemodule_from_id('mathetrainer', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$instance = $DB->get_record('mathetrainer', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/mathetrainer:exportpackage', $context);

$url = new moodle_url('/mod/mathetrainer/bloom.php', ['id' => $cm->id]);
$PAGE->set_url($url);
$PAGE->set_title(format_string($instance->name) . ': ' . get_string('bloompackage', 'mathetrainer'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($instance);

$styles = [
    package_builder::STYLE_MIXED => get_string('bloomstyle_mixed', 'mathetrainer'),
    package_builder::STYLE_MC => get_string('bloomstyle_mc', 'mathetrainer'),
    package_builder::STYLE_INPUT => get_string('bloomstyle_input', 'mathetrainer'),
];
if (!isset($styles[$style])) {
    $style = package_builder::STYLE_MIXED;
}
$count = max(1, min(30, $count));
$templates = (new attempt_manager($DB, $instance))->select_templates();

if ($download) {
    require_sesskey();
    $builder = new package_builder(static fn(string $key, $a = null): string => get_string($key, 'mathetrainer', $a));
    $meta = ['title' => format_string($instance->name), 'grade' => (int)$instance->gradelevel];
    if (!empty($instance->competencyid)) {
        $meta['competency'] = $instance->competencyid;
    }
    $package = $builder->build($templates, $count, $seed ?: random_int(1, 999999), $style, $meta);
    $filename = clean_filename(format_string($instance->name)) . '-bloom.json';
    send_file(
        json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        $filename,
        0,
        0,
        true,
        true,
        'application/json'
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('bloompackage', 'mathetrainer'), 3);
echo html_writer::tag('p', get_string('bloompackage_intro', 'mathetrainer'));

$names = array_map(static fn(string $t): string => get_string('template_' . $t, 'mathetrainer'), $templates);
$label = html_writer::tag('strong', get_string('exercisetypes', 'mathetrainer') . ': ');
echo html_writer::tag('p', $label . s(implode(', ', $names)));

echo html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false), 'class' => 'mb-3']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'download', 'value' => 1]);

echo html_writer::start_div('mb-2');
echo html_writer::label(get_string('bloomcount', 'mathetrainer'), 'mathetrainer_count', true, ['class' => 'mr-2 me-2']);
echo html_writer::empty_tag('input', ['type' => 'number', 'name' => 'count', 'id' => 'mathetrainer_count', 'value' => $count,
    'min' => 1, 'max' => 30, 'class' => 'form-control d-inline-block', 'style' => 'width: 6rem']);
echo html_writer::end_div();

echo html_writer::start_div('mb-2');
echo html_writer::label(get_string('bloomstyle', 'mathetrainer'), 'mathetrainer_style', true, ['class' => 'mr-2 me-2']);
echo html_writer::select($styles, 'style', $style, false, ['id' => 'mathetrainer_style', 'class' => 'custom-select']);
echo html_writer::end_div();

echo html_writer::start_div('mb-3');
echo html_writer::label(get_string('bloomseed', 'mathetrainer'), 'mathetrainer_seed', true, ['class' => 'mr-2 me-2']);
echo html_writer::empty_tag('input', ['type' => 'number', 'name' => 'seed', 'id' => 'mathetrainer_seed',
    'value' => $seed ?: '', 'min' => 0, 'class' => 'form-control d-inline-block', 'style' => 'width: 10rem']);
echo html_writer::tag('small', get_string('bloomseed_help', 'mathetrainer'), ['class' => 'form-text text-muted']);
echo html_writer::end_div();

echo html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-primary',
    'value' => get_string('bloomdownload', 'mathetrainer')]);
echo html_writer::end_tag('form');

echo $OUTPUT->notification(get_string('bloomprivacy', 'mathetrainer'), 'info', false);
echo $OUTPUT->footer();
