<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Creates grade items and writes grades for all activities.
 *
 * Queued by the upgrade to gradebook support: the gradebook must not be
 * changed while Moodle is upgrading, so this runs afterwards via cron.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

namespace mod_mathetrainer\task;

/**
 * Ad-hoc task that updates the grade items and grades of all mathetrainer activities.
 */
class update_all_grades extends \core\task\adhoc_task {
    /**
     * Updates the grades of every activity instance.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/mathetrainer/lib.php');

        $instances = $DB->get_recordset('mathetrainer');
        foreach ($instances as $instance) {
            mathetrainer_update_grades($instance);
        }
        $instances->close();
    }
}
