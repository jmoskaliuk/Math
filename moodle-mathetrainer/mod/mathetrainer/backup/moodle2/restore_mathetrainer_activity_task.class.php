<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Restore task for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/mathetrainer/backup/moodle2/restore_mathetrainer_stepslib.php');

/**
 * Restore task that adds the mathetrainer structure step and the decode and log rules.
 */
class restore_mathetrainer_activity_task extends restore_activity_task {
    /**
     * No activity-specific restore settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Adds the structure step that reads mathetrainer.xml.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_mathetrainer_activity_structure_step('mathetrainer_structure', 'mathetrainer.xml'));
    }

    /**
     * Contents with links to decode: the intro of the activity.
     *
     * @return restore_decode_content[]
     */
    public static function define_decode_contents() {
        return [new restore_decode_content('mathetrainer', ['intro'], 'mathetrainer')];
    }

    /**
     * Rules that turn encoded links back into view.php and index.php links.
     *
     * @return restore_decode_rule[]
     */
    public static function define_decode_rules() {
        return [
            new restore_decode_rule('MATHETRAINERVIEWBYID', '/mod/mathetrainer/view.php?id=$1', 'course_module'),
            new restore_decode_rule('MATHETRAINERINDEX', '/mod/mathetrainer/index.php?id=$1', 'course'),
        ];
    }

    /**
     * Log rules for the activity "view" action.
     *
     * @return restore_log_rule[]
     */
    public static function define_restore_log_rules() {
        return [new restore_log_rule('mathetrainer', 'view', 'view.php?id={course_module}', '{mathetrainer}')];
    }

    /**
     * Log rules for the course-level "view all" action.
     *
     * @return restore_log_rule[]
     */
    public static function define_restore_log_rules_for_course() {
        return [new restore_log_rule('mathetrainer', 'view all', 'index.php?id={course}', null)];
    }
}
