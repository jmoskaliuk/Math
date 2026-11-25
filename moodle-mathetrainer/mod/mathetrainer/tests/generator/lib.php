<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Test data generator for mod_mathetrainer.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

/**
 * Test data generator for mathetrainer activities and finished attempts.
 */
class mod_mathetrainer_generator extends testing_module_generator {
    /**
     * Creates an activity instance with default settings for missing fields.
     *
     * @param array|stdClass|null $record Instance settings
     * @param array|null $options Options for the course module
     * @return stdClass The created instance
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (object)(array)$record;
        $defaults = [
            'state' => 'NRW',
            'gradelevel' => 7,
            'topic' => '',
            'competencyid' => '',
            'requirementlevel' => '',
            'grade' => 100,
            'grademethod' => \mod_mathetrainer\local\grading::METHOD_HIGHEST,
        ];
        foreach ($defaults as $name => $value) {
            if (!isset($record->$name)) {
                $record->$name = $value;
            }
        }
        return parent::create_instance($record, (array)$options);
    }

    /**
     * Creates a finished round for a user with the given number of correct answers.
     *
     * @param stdClass $instance activity record
     * @param int $userid learner
     * @param int $correct number of correct answers
     * @return stdClass the attempt
     */
    public function create_finished_attempt(stdClass $instance, int $userid, int $correct): stdClass {
        $manager = new \mod_mathetrainer\local\attempt_manager($GLOBALS['DB'], $instance);
        $attempt = $manager->start_attempt($userid);
        $answers = [];
        foreach ($manager->get_items((int)$attempt->id) as $slot => $item) {
            $right = $item->expected['display'] ?? $item->expected['answer'];
            $answers[$slot] = $slot <= $correct ? (string)$right : 'x';
        }
        $attempt = $manager->submit($attempt, $answers);
        mathetrainer_update_grades($instance, $userid);
        return $attempt;
    }
}
