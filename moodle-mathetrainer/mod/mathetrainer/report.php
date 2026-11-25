<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Teacher report: results per learner and exercise type, typical errors and
 * practice recommendations. Can be downloaded (CSV, Excel, ODS, ...).
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_mathetrainer\local\report_data;

$id = required_param('id', PARAM_INT);
$download = optional_param('download', '', PARAM_ALPHA);

$cm = get_coursemodule_from_id('mathetrainer', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$instance = $DB->get_record('mathetrainer', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/mathetrainer:viewreports', $context);

$url = new moodle_url('/mod/mathetrainer/report.php', ['id' => $cm->id]);
$PAGE->set_url($url);
$PAGE->set_title(format_string($instance->name) . ': ' . get_string('report', 'mathetrainer'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_activity_record($instance);

$currentgroup = groups_get_activity_group($cm, true);
$users = get_enrolled_users(
    $context,
    'mod/mathetrainer:attempt',
    (int)$currentgroup,
    'u.*',
    'u.lastname, u.firstname',
    0,
    0,
    true
);

$data = new report_data($DB, $instance);
$rows = $data->rows($users);
$classstats = $data->class_template_stats();
$templateids = array_keys($classstats);

$percent = static fn(?float $value): string => $value === null ? '–' : format_float($value * 100, 0) . ' %';
$templatename = static function (string $templateid): string {
    return get_string_manager()->string_exists('template_' . $templateid, 'mathetrainer')
        ? get_string('template_' . $templateid, 'mathetrainer')
        : $templateid;
};

if ($download !== '') {
    $columns = [
        'fullname' => get_string('fullname'),
        'attempts' => get_string('attempts', 'mathetrainer'),
        'grade' => get_string('aggregatedscore', 'mathetrainer'),
        'lastactivity' => get_string('lastattempt', 'mathetrainer'),
    ];
    foreach ($templateids as $templateid) {
        $columns['t_' . $templateid] = $templatename($templateid);
    }
    $columns['recommendation'] = get_string('recommendation', 'mathetrainer');

    \core\dataformat::download_data(
        clean_filename(format_string($instance->name) . '-' . get_string('report', 'mathetrainer')),
        $download,
        $columns,
        new ArrayIterator($rows),
        static function (stdClass $row) use ($templateids, $percent): array {
            $record = [
                'fullname' => fullname($row->user),
                'attempts' => $row->attempts,
                'grade' => $percent($row->grade),
                'lastactivity' => $row->lastactivity ? userdate($row->lastactivity) : '',
            ];
            foreach ($templateids as $templateid) {
                $stats = $row->templates[$templateid] ?? null;
                $record['t_' . $templateid] = $stats ? $percent($stats['correct'] / $stats['total']) : '';
            }
            $record['recommendation'] = mathetrainer_recommendation_text($row->recommendation, false);
            return $record;
        }
    );
    exit;
}

echo $OUTPUT->header();
groups_print_activity_menu($cm, $url);
echo $OUTPUT->heading(get_string('report', 'mathetrainer'), 3);

if (!$users) {
    echo $OUTPUT->notification(get_string('nolearners', 'mathetrainer'), 'info');
    echo $OUTPUT->footer();
    exit;
}

// Class overview per exercise type.
if ($classstats) {
    echo $OUTPUT->heading(get_string('classoverview', 'mathetrainer'), 4);
    $table = new html_table();
    $table->head = [get_string('exercisetype', 'mathetrainer'), get_string('answers', 'mathetrainer'),
        get_string('successrate', 'mathetrainer')];
    foreach ($classstats as $templateid => $stats) {
        $table->data[] = [$templatename($templateid), $stats['total'], $percent($stats['correct'] / $stats['total'])];
    }
    echo html_writer::table($table);
}

// Per learner.
echo $OUTPUT->heading(get_string('learneroverview', 'mathetrainer'), 4);
$table = new html_table();
$table->attributes['class'] = 'generaltable mathetrainer-report';
$table->head = [get_string('fullname'), get_string('attempts', 'mathetrainer'), get_string('aggregatedscore', 'mathetrainer')];
foreach ($templateids as $templateid) {
    $table->head[] = $templatename($templateid);
}
$table->head[] = get_string('recommendation', 'mathetrainer');

foreach ($rows as $row) {
    $cells = [
        fullname($row->user),
        $row->attempts,
        $percent($row->grade),
    ];
    foreach ($templateids as $templateid) {
        $stats = $row->templates[$templateid] ?? null;
        if (!$stats) {
            $cells[] = '–';
            continue;
        }
        $rate = $stats['correct'] / $stats['total'];
        $class = $rate >= 0.8 ? 'text-success' : ($rate < 0.6 ? 'text-danger' : '');
        $cells[] = html_writer::span($percent($rate), $class) . html_writer::tag(
            'small',
            ' (' . $stats['correct'] . '/' . $stats['total'] . ')',
            ['class' => 'text-muted']
        );
    }
    $cells[] = s(mathetrainer_recommendation_text($row->recommendation, false));
    $table->data[] = $cells;
}
echo html_writer::div(html_writer::table($table), 'table-responsive');

echo $OUTPUT->download_dataformat_selector(
    get_string('downloadreport', 'mathetrainer'),
    $url->out_omit_querystring(),
    'download',
    ['id' => $cm->id]
);

echo html_writer::tag('p', get_string('recommendationrules', 'mathetrainer'), ['class' => 'text-muted small mt-3']);

echo $OUTPUT->footer();
