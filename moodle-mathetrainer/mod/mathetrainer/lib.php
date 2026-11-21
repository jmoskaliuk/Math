<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Library of Moodle callbacks for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Features supported by this module.
 *
 * Backup/restore is intentionally not declared yet: there are no backup
 * classes, and declaring it would break course backups.
 */
function mathetrainer_supports(string $feature): bool|string|null {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_BACKUP_MOODLE2 => false,
        FEATURE_GRADE_HAS_GRADE => false,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_ASSESSMENT,
        default => null,
    };
}

/**
 * Normalises form data to the database record.
 */
function mathetrainer_prepare_record(stdClass $data): stdClass {
    $data->topic = isset($data->topic) && $data->topic !== '' ? (string)$data->topic : null;
    $data->competencyid = isset($data->competencyid) && trim((string)$data->competencyid) !== ''
        ? trim((string)$data->competencyid) : null;
    $data->requirementlevel = isset($data->requirementlevel) && $data->requirementlevel !== ''
        ? (string)$data->requirementlevel : null;
    $data->schoolform = isset($data->schoolform) && $data->schoolform !== '' ? (string)$data->schoolform : null;
    $data->gradelevel = (int)($data->gradelevel ?? 5);
    $data->state = (string)($data->state ?? 'NRW');
    $data->timemodified = time();
    return $data;
}

function mathetrainer_add_instance(stdClass $data, $mform = null): int {
    global $DB;
    $data = mathetrainer_prepare_record($data);
    $data->timecreated = $data->timemodified;
    return (int)$DB->insert_record('mathetrainer', $data);
}

function mathetrainer_update_instance(stdClass $data, $mform = null): bool {
    global $DB;
    $data->id = $data->instance;
    $data = mathetrainer_prepare_record($data);
    return $DB->update_record('mathetrainer', $data);
}

function mathetrainer_delete_instance(int $id): bool {
    global $DB;
    if (!$DB->record_exists('mathetrainer', ['id' => $id])) {
        return false;
    }
    \mod_mathetrainer\local\attempt_manager::delete_instance_data($DB, $id);
    $DB->delete_records('mathetrainer', ['id' => $id]);
    return true;
}

/**
 * Course reset: adds the "delete all attempts" checkbox.
 */
function mathetrainer_reset_course_form_definition(&$mform): void {
    $mform->addElement('header', 'mathetrainerheader', get_string('modulenameplural', 'mathetrainer'));
    $mform->addElement('advcheckbox', 'reset_mathetrainer_attempts', get_string('resetattempts', 'mathetrainer'));
}

function mathetrainer_reset_course_form_defaults($course): array {
    return ['reset_mathetrainer_attempts' => 1];
}

function mathetrainer_reset_userdata($data): array {
    global $DB;
    $status = [];
    if (!empty($data->reset_mathetrainer_attempts)) {
        foreach ($DB->get_fieldset_select('mathetrainer', 'id', 'course = ?', [$data->courseid]) as $id) {
            \mod_mathetrainer\local\attempt_manager::delete_instance_data($DB, (int)$id);
        }
        $status[] = [
            'component' => get_string('modulenameplural', 'mathetrainer'),
            'item' => get_string('resetattempts', 'mathetrainer'),
            'error' => false,
        ];
    }
    return $status;
}
