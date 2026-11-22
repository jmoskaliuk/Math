<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Backup task for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/mathetrainer/backup/moodle2/backup_mathetrainer_stepslib.php');

class backup_mathetrainer_activity_task extends backup_activity_task {
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new backup_mathetrainer_activity_structure_step('mathetrainer_structure', 'mathetrainer.xml'));
    }

    /**
     * Encodes links to the activity so they can be restored in another course.
     *
     * @param string $content
     * @return string
     */
    public static function encode_content_links($content) {
        global $CFG;
        $base = preg_quote($CFG->wwwroot, '/');
        $content = preg_replace('/(' . $base . '\/mod\/mathetrainer\/index.php\?id\=)([0-9]+)/',
            '$@MATHETRAINERINDEX*$2@$', $content);
        $content = preg_replace('/(' . $base . '\/mod\/mathetrainer\/view.php\?id\=)([0-9]+)/',
            '$@MATHETRAINERVIEWBYID*$2@$', $content);
        return $content;
    }
}
