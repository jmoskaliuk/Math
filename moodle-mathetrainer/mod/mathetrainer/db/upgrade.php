<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Upgrade steps for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

/**
 * Runs the upgrade steps for mod_mathetrainer.
 *
 * @param int $oldversion Version of the plugin before the upgrade
 * @return bool Always true
 */
function xmldb_mathetrainer_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026112500) {
        // Gradebook support: maximum grade and grading method.
        $table = new xmldb_table('mathetrainer');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '100', 'requirementlevel');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('grademethod', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1', 'grade');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Existing activities get their grade item and the grades of finished rounds. The gradebook
        // must not be changed during an upgrade, so this is done by an adhoc task afterwards (cron).
        \core\task\manager::queue_adhoc_task(new \mod_mathetrainer\task\update_all_grades(), true);

        upgrade_mod_savepoint(true, 2026112500, 'mathetrainer');
    }

    return true;
}
