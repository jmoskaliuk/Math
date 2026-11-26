<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Backup structure for mod_mathetrainer.
 *
 * Settings are always included. With user data: attempts with their items and
 * responses, and mastery values. The generation log is instance-specific audit
 * data and is not backed up.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

/**
 * Structure step that defines the backup tree of an activity.
 */
class backup_mathetrainer_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the backup structure (with user data only when included).
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $mathetrainer = new backup_nested_element('mathetrainer', ['id'], [
            'name', 'intro', 'introformat', 'state', 'schoolform', 'gradelevel', 'topic',
            'competencyid', 'requirementlevel', 'grade', 'grademethod', 'timecreated', 'timemodified',
        ]);

        $attempts = new backup_nested_element('attempts');
        $attempt = new backup_nested_element('attempt', ['id'], [
            'userid', 'attemptno', 'status', 'score', 'timecreated', 'timemodified',
        ]);

        $items = new backup_nested_element('items');
        $item = new backup_nested_element('item', ['id'], [
            'slot', 'templateid', 'seed', 'payloadjson', 'expectedjson',
        ]);

        $responses = new backup_nested_element('responses');
        $response = new backup_nested_element('response', ['id'], [
            'userid', 'responsejson', 'iscorrect', 'feedback', 'timesubmitted',
        ]);

        $masteries = new backup_nested_element('masteries');
        $masteryrecord = new backup_nested_element('masteryrecord', ['id'], [
            'userid', 'competencyid', 'mastery', 'attemptcount', 'timemodified',
        ]);

        $mathetrainer->add_child($attempts);
        $attempts->add_child($attempt);
        $attempt->add_child($items);
        $items->add_child($item);
        $item->add_child($responses);
        $responses->add_child($response);
        $mathetrainer->add_child($masteries);
        $masteries->add_child($masteryrecord);

        $mathetrainer->set_source_table('mathetrainer', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $attempt->set_source_table('mathetrainer_attempts', ['mathetrainerid' => backup::VAR_PARENTID], 'id ASC');
            $item->set_source_table('mathetrainer_items', ['attemptid' => backup::VAR_PARENTID], 'slot ASC');
            $response->set_source_table('mathetrainer_responses', ['itemid' => backup::VAR_PARENTID], 'id ASC');
            $masteryrecord->set_source_table('mathetrainer_mastery', ['mathetrainerid' => backup::VAR_PARENTID], 'id ASC');
        }

        $mathetrainer->annotate_ids('scale', 'grade');
        $attempt->annotate_ids('user', 'userid');
        $response->annotate_ids('user', 'userid');
        $masteryrecord->annotate_ids('user', 'userid');

        $mathetrainer->annotate_files('mod_mathetrainer', 'intro', null);

        return $this->prepare_activity_structure($mathetrainer);
    }
}
