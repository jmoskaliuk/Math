<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Restore structure for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

/**
 * Structure step that restores an activity and, with user data, its attempts and mastery values.
 */
class restore_mathetrainer_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the paths to restore (user data paths only when included).
     *
     * @return restore_path_element[]
     */
    protected function define_structure() {
        $paths = [new restore_path_element('mathetrainer', '/activity/mathetrainer')];

        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('mathetrainer_attempt', '/activity/mathetrainer/attempts/attempt');
            $paths[] = new restore_path_element('mathetrainer_item', '/activity/mathetrainer/attempts/attempt/items/item');
            $paths[] = new restore_path_element(
                'mathetrainer_response',
                '/activity/mathetrainer/attempts/attempt/items/item/responses/response'
            );
            $paths[] = new restore_path_element('mathetrainer_mastery', '/activity/mathetrainer/masteries/masteryrecord');
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the activity record; scales in the grade are mapped.
     *
     * @param array $data Backed-up record
     */
    protected function process_mathetrainer($data) {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        // Scales are course- or site-specific and have to be mapped.
        if (isset($data->grade) && $data->grade < 0) {
            $data->grade = -($this->get_mappingid('scale', abs($data->grade)) ?: 0);
        }
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $newid = $DB->insert_record('mathetrainer', $data);
        $this->apply_activity_instance($newid);
    }

    /**
     * Restores an attempt; skipped if its user was not restored.
     *
     * @param array $data Backed-up record
     */
    protected function process_mathetrainer_attempt($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->mathetrainerid = $this->get_new_parentid('mathetrainer');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!$data->userid) {
            return;
        }
        $newid = $DB->insert_record('mathetrainer_attempts', $data);
        $this->set_mapping('mathetrainer_attempt', $oldid, $newid);
    }

    /**
     * Restores an item of an attempt; skipped if its attempt was skipped.
     *
     * @param array $data Backed-up record
     */
    protected function process_mathetrainer_item($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->attemptid = $this->get_new_parentid('mathetrainer_attempt');
        if (!$data->attemptid) {
            // Attempt was skipped (user not restored).
            return;
        }
        $newid = $DB->insert_record('mathetrainer_items', $data);
        $this->set_mapping('mathetrainer_item', $oldid, $newid);
    }

    /**
     * Restores a response; skipped if its item or user was not restored.
     *
     * @param array $data Backed-up record
     */
    protected function process_mathetrainer_response($data) {
        global $DB;
        $data = (object)$data;
        $data->itemid = $this->get_new_parentid('mathetrainer_item');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!$data->itemid || !$data->userid) {
            return;
        }
        $DB->insert_record('mathetrainer_responses', $data);
    }

    /**
     * Restores a mastery value; skipped if its user was not restored.
     *
     * @param array $data Backed-up record
     */
    protected function process_mathetrainer_mastery($data) {
        global $DB;
        $data = (object)$data;
        $data->mathetrainerid = $this->get_new_parentid('mathetrainer');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!$data->userid) {
            return;
        }
        $DB->insert_record('mathetrainer_mastery', $data);
    }

    /**
     * Restores the files of the intro.
     */
    protected function after_execute() {
        $this->add_related_files('mod_mathetrainer', 'intro', null);
    }
}
