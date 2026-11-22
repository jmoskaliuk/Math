<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Restore structure for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

class restore_mathetrainer_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = [new restore_path_element('mathetrainer', '/activity/mathetrainer')];

        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('mathetrainer_attempt', '/activity/mathetrainer/attempts/attempt');
            $paths[] = new restore_path_element('mathetrainer_item', '/activity/mathetrainer/attempts/attempt/items/item');
            $paths[] = new restore_path_element('mathetrainer_response',
                '/activity/mathetrainer/attempts/attempt/items/item/responses/response');
            $paths[] = new restore_path_element('mathetrainer_mastery', '/activity/mathetrainer/masteries/masteryrecord');
        }

        return $this->prepare_activity_structure($paths);
    }

    protected function process_mathetrainer($data) {
        global $DB;
        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $newid = $DB->insert_record('mathetrainer', $data);
        $this->apply_activity_instance($newid);
    }

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

    protected function after_execute() {
        $this->add_related_files('mod_mathetrainer', 'intro', null);
    }
}
