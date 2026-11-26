<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Upgrade steps for local_curriculum_math.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

/**
 * Runs the upgrade steps for local_curriculum_math.
 *
 * @param int $oldversion Version of the plugin before the upgrade
 * @return bool Always true
 */
function xmldb_local_curriculum_math_upgrade(int $oldversion): bool {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026112600) {
        // Moodle convention: plugin tables start with the plugin name. Rename existing tables, keep their data.
        $renames = [
            'curriculum_source' => 'local_curriculum_math_source',
            'curriculum_framework' => 'local_curriculum_math_framework',
            'curriculum_node' => 'local_curriculum_math_node',
            'curriculum_competency' => 'local_curriculum_math_competency',
            'curriculum_mapping' => 'local_curriculum_math_mapping',
            'exercise_template' => 'local_curriculum_math_template',
            'exercise_competency_link' => 'local_curriculum_math_templatelink',
        ];
        foreach ($renames as $old => $new) {
            $table = new xmldb_table($old);
            if ($dbman->table_exists($table) && !$dbman->table_exists(new xmldb_table($new))) {
                $dbman->rename_table($table, $new);
            }
        }
        upgrade_plugin_savepoint(true, 2026112600, 'local', 'curriculum_math');
    }

    return true;
}
