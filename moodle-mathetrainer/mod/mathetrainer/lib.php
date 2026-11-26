<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Library of Moodle callbacks for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

/**
 * Features supported by this module.
 *
 * @param string $feature FEATURE_xx constant
 * @return bool|string|null true/false or a value if supported, null if unknown
 */
function mathetrainer_supports(string $feature): bool|string|null {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_GRADE_HAS_GRADE => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_ASSESSMENT,
        default => null,
    };
}

/**
 * Normalises form data to the database record.
 *
 * @param stdClass $data form data
 * @return stdClass database record
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
    $data->grade = (int)($data->grade ?? 100);
    $data->grademethod = (int)($data->grademethod ?? \mod_mathetrainer\local\grading::METHOD_HIGHEST);
    $data->timemodified = time();
    return $data;
}

/**
 * Creates a new activity instance and its grade item.
 *
 * @param stdClass $data Data from the settings form
 * @param mixed $mform The settings form (unused)
 * @return int Id of the new instance
 */
function mathetrainer_add_instance(stdClass $data, $mform = null): int {
    global $DB;
    $data = mathetrainer_prepare_record($data);
    $data->timecreated = $data->timemodified;
    $data->id = (int)$DB->insert_record('mathetrainer', $data);
    mathetrainer_grade_item_update($data);
    return $data->id;
}

/**
 * Updates an activity instance and recalculates all its grades.
 *
 * @param stdClass $data Data from the settings form
 * @param mixed $mform The settings form (unused)
 * @return bool Always true
 */
function mathetrainer_update_instance(stdClass $data, $mform = null): bool {
    global $DB;
    $data->id = $data->instance;
    $data = mathetrainer_prepare_record($data);
    $DB->update_record('mathetrainer', $data);
    // Maximum grade or grading method may have changed: recalculate all grades.
    $instance = $DB->get_record('mathetrainer', ['id' => $data->id]);
    if (isset($data->cmidnumber)) {
        $instance->cmidnumber = $data->cmidnumber;
    }
    mathetrainer_update_grades($instance, 0, false);
    return true;
}

/**
 * Deletes an activity instance with all attempts, mastery data and its grade item.
 *
 * @param int $id Id of the instance
 * @return bool False if the instance does not exist
 */
function mathetrainer_delete_instance(int $id): bool {
    global $DB;
    if (!$DB->record_exists('mathetrainer', ['id' => $id])) {
        return false;
    }
    $instance = $DB->get_record('mathetrainer', ['id' => $id]);
    \mod_mathetrainer\local\attempt_manager::delete_instance_data($DB, $id);
    mathetrainer_grade_item_delete($instance);
    $DB->delete_records('mathetrainer', ['id' => $id]);
    return true;
}

/**
 * Creates or updates the grade item of an activity.
 *
 * @param stdClass $instance activity record (with cmidnumber when called from the settings form)
 * @param array|string|null $grades grades to write, 'reset' to reset all grades
 */
function mathetrainer_grade_item_update(stdClass $instance, $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = ['itemname' => $instance->name];
    if (isset($instance->cmidnumber)) {
        $params['idnumber'] = $instance->cmidnumber;
    }
    $grade = (int)($instance->grade ?? 0);
    if ($grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $grade;
        $params['grademin'] = 0;
    } else if ($grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }
    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/mathetrainer', $instance->course, 'mod', 'mathetrainer', $instance->id, 0, $grades, $params);
}

/**
 * Deletes the grade item of an activity.
 *
 * @param stdClass $instance Activity record
 * @return int Result of grade_update()
 */
function mathetrainer_grade_item_delete(stdClass $instance): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update('mod/mathetrainer', $instance->course, 'mod', 'mathetrainer', $instance->id, 0, null, ['deleted' => 1]);
}

/**
 * Grades of learners from their finished attempts, aggregated by the grading method.
 *
 * @param stdClass $instance activity record
 * @param int $userid only this learner, 0 for all
 * @return array<int, stdClass> userid => (userid, rawgrade)
 */
function mathetrainer_get_user_grades(stdClass $instance, int $userid = 0): array {
    global $DB;

    $params = ['mathetrainerid' => $instance->id, 'status' => \mod_mathetrainer\local\attempt_manager::STATUS_FINISHED];
    if ($userid) {
        $params['userid'] = $userid;
    }
    $scores = [];
    foreach ($DB->get_records('mathetrainer_attempts', $params, 'userid, attemptno', 'id, userid, score') as $attempt) {
        $scores[(int)$attempt->userid][] = (float)$attempt->score;
    }

    $scaleitems = 0;
    if ((int)$instance->grade < 0 && ($scale = $DB->get_record('scale', ['id' => -(int)$instance->grade]))) {
        $scaleitems = count(explode(',', $scale->scale));
    }

    $grades = [];
    foreach ($scores as $uid => $list) {
        $score = \mod_mathetrainer\local\grading::aggregate($list, (int)$instance->grademethod);
        $grades[$uid] = (object)[
            'userid' => $uid,
            'rawgrade' => $score === null ? null
                : \mod_mathetrainer\local\grading::rawgrade($score, (int)$instance->grade, $scaleitems),
        ];
    }
    return $grades;
}

/**
 * Writes grades to the gradebook (all learners or one).
 *
 * @param stdClass $instance activity record
 * @param int $userid only this learner, 0 for all
 * @param bool $nullifnone write a null grade if the learner has none
 */
function mathetrainer_update_grades(stdClass $instance, int $userid = 0, bool $nullifnone = true): void {
    if ((int)($instance->grade ?? 0) === 0) {
        mathetrainer_grade_item_update($instance);
    } else if ($grades = mathetrainer_get_user_grades($instance, $userid)) {
        mathetrainer_grade_item_update($instance, $grades);
    } else if ($userid && $nullifnone) {
        mathetrainer_grade_item_update($instance, [$userid => (object)['userid' => $userid, 'rawgrade' => null]]);
    } else {
        mathetrainer_grade_item_update($instance);
    }
}

/**
 * Course reset: adds the "delete all attempts" checkbox.
 *
 * @param MoodleQuickForm $mform course reset form
 */
function mathetrainer_reset_course_form_definition(&$mform): void {
    $mform->addElement('header', 'mathetrainerheader', get_string('modulenameplural', 'mathetrainer'));
    $mform->addElement('advcheckbox', 'reset_mathetrainer_attempts', get_string('resetattempts', 'mathetrainer'));
}

/**
 * Course reset: the "delete all attempts" checkbox is checked by default.
 *
 * @param stdClass $course Course being reset
 * @return array Default form values
 */
function mathetrainer_reset_course_form_defaults($course): array {
    return ['reset_mathetrainer_attempts' => 1];
}

/**
 * Course reset: deletes all attempts and mastery data if requested and resets the grades.
 *
 * @param stdClass $data Data from the reset form
 * @return array Status entries for the reset report
 */
function mathetrainer_reset_userdata($data): array {
    global $DB;
    $status = [];
    if (!empty($data->reset_mathetrainer_attempts)) {
        foreach ($DB->get_records('mathetrainer', ['course' => $data->courseid]) as $instance) {
            \mod_mathetrainer\local\attempt_manager::delete_instance_data($DB, (int)$instance->id);
            if (empty($data->reset_gradebook_grades)) {
                mathetrainer_grade_item_update($instance, 'reset');
            }
        }
        $status[] = [
            'component' => get_string('modulenameplural', 'mathetrainer'),
            'item' => get_string('resetattempts', 'mathetrainer'),
            'error' => false,
        ];
    }
    return $status;
}

/**
 * Readable recommendation from {@see \mod_mathetrainer\local\recommender::recommend()}.
 *
 * @param array $recommendation
 * @param bool $forlearner addressed to the learner (du) instead of the teacher
 */
function mathetrainer_recommendation_text(array $recommendation, bool $forlearner): string {
    $prefix = $forlearner ? 'learnerrec_' : 'rec_';
    $strings = get_string_manager();
    $name = static function (string $key, string $prefix) use ($strings): string {
        return $strings->string_exists($prefix . $key, 'mathetrainer') ? get_string($prefix . $key, 'mathetrainer') : $key;
    };

    $parts = [];
    switch ($recommendation['status']) {
        case 'notstarted':
            return get_string($prefix . 'notstarted', 'mathetrainer');
        case 'mastered':
            return get_string($prefix . 'mastered', 'mathetrainer');
        case 'ontrack':
            $parts[] = get_string($prefix . 'ontrack', 'mathetrainer');
            break;
    }
    if (!empty($recommendation['repeat'])) {
        $templates = array_map(static fn(string $t): string => $name($t, 'template_'), $recommendation['repeat']);
        $parts[] = get_string($prefix . 'repeat', 'mathetrainer', implode(', ', $templates));
    }
    if (!empty($recommendation['focus'])) {
        $parts[] = get_string($prefix . 'focus', 'mathetrainer', $name($recommendation['focus'], 'errorname_'));
    }
    return implode(' ', $parts);
}

/**
 * Adds the report to the activity navigation.
 *
 * @param settings_navigation $settings settings navigation
 * @param navigation_node $node activity node
 */
function mathetrainer_extend_settings_navigation(settings_navigation $settings, navigation_node $node): void {
    $cm = $settings->get_page()->cm;
    if ($cm && has_capability('mod/mathetrainer:viewreports', $cm->context)) {
        $node->add(
            get_string('report', 'mathetrainer'),
            new moodle_url('/mod/mathetrainer/report.php', ['id' => $cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mathetrainer_report',
            new pix_icon('i/report', '')
        );
    }
    if ($cm && has_capability('mod/mathetrainer:exportpackage', $cm->context)) {
        $node->add(
            get_string('bloompackage', 'mathetrainer'),
            new moodle_url('/mod/mathetrainer/bloom.php', ['id' => $cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mathetrainer_bloom',
            new pix_icon('i/export', '')
        );
    }
}
