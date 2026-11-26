<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use mod_mathetrainer\local\attempt_manager;
use mod_mathetrainer\local\grading;
use mod_mathetrainer\local\report_data;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/mathetrainer/lib.php');
require_once($CFG->libdir . '/gradelib.php');

/**
 * Integration tests with database: attempts, gradebook, report, deletion.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\local\attempt_manager
 * @covers     \mod_mathetrainer\local\report_data
 * @covers     ::mathetrainer_update_grades
 * @license    MIT
 */
final class lib_test extends \advanced_testcase {
    /**
     * Creates a course with an enrolled student and an activity.
     *
     * @param array $settings Activity settings
     * @return array [course, student, instance]
     */
    private function setup_activity(array $settings = []): array {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $instance = $this->getDataGenerator()->create_module('mathetrainer', ['course' => $course->id] + $settings);
        return [$course, $student, $instance];
    }

    public function test_attempt_lifecycle(): void {
        global $DB;
        [, $student, $instance] = $this->setup_activity(['topic' => 'Rationale Zahlen', 'gradelevel' => 7]);
        $manager = new attempt_manager($DB, $instance);

        $attempt = $manager->start_attempt($student->id);
        $this->assertEquals($attempt->id, $manager->start_attempt($student->id)->id, 'Running attempt is reused.');
        $items = $manager->get_items((int)$attempt->id);
        $this->assertCount(attempt_manager::ITEMS_PER_ATTEMPT, $items);
        foreach ($items as $item) {
            $this->assertContains($item->templateid, ['integer_numberline', 'rational_addition', 'sign_error_analysis']);
        }
        $this->assertSame(
            attempt_manager::ITEMS_PER_ATTEMPT,
            $DB->count_records('mathetrainer_generation_log', ['attemptid' => $attempt->id])
        );

        $answers = [];
        foreach ($items as $slot => $item) {
            $answers[$slot] = $slot <= 3 ? $item->expected['answer'] : '';
        }
        $finished = $manager->submit($attempt, $answers);
        $this->assertSame(attempt_manager::STATUS_FINISHED, $finished->status);
        $this->assertEqualsWithDelta(0.6, (float)$finished->score, 0.0001);
        $this->assertSame(3, $DB->count_records('mathetrainer_responses', ['userid' => $student->id, 'iscorrect' => 1]));

        $mastery = $DB->get_record('mathetrainer_mastery', ['mathetrainerid' => $instance->id, 'userid' => $student->id]);
        $this->assertSame('topic:Rationale Zahlen', $mastery->competencyid);
        $this->assertEqualsWithDelta(0.6, (float)$mastery->mastery, 0.0001);

        $this->expectException(\moodle_exception::class);
        $manager->submit($finished, $answers);
    }

    public function test_gradebook(): void {
        [$course, $student, $instance] = $this->setup_activity(['grademethod' => grading::METHOD_AVERAGE, 'grade' => 20]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_mathetrainer');
        $generator->create_finished_attempt($instance, (int)$student->id, 5);
        $generator->create_finished_attempt($instance, (int)$student->id, 0);

        $grades = grade_get_grades($course->id, 'mod', 'mathetrainer', $instance->id, $student->id);
        $item = reset($grades->items);
        $this->assertEquals(20, $item->grademax);
        $this->assertEqualsWithDelta(10.0, (float)$item->grades[$student->id]->grade, 0.0001, 'Average of 20 and 0.');

        $instance->grademethod = grading::METHOD_HIGHEST;
        mathetrainer_update_grades($instance);
        $grades = grade_get_grades($course->id, 'mod', 'mathetrainer', $instance->id, $student->id);
        $this->assertEqualsWithDelta(20.0, (float)reset($grades->items)->grades[$student->id]->grade, 0.0001);
    }

    public function test_report_and_recommendation(): void {
        global $DB;
        [, $student, $instance] = $this->setup_activity(['topic' => 'Brüche', 'gradelevel' => 6]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_mathetrainer');
        $generator->create_finished_attempt($instance, (int)$student->id, 0);
        $generator->create_finished_attempt($instance, (int)$student->id, 1);

        $rows = (new report_data($DB, $instance))->rows([$student->id => $student]);
        $this->assertCount(1, $rows);
        $this->assertSame(2, $rows[0]->attempts);
        $this->assertSame('practise', $rows[0]->recommendation['status']);
        $this->assertNotEmpty($rows[0]->recommendation['repeat']);
        $this->assertNotEmpty(mathetrainer_recommendation_text($rows[0]->recommendation, true));
    }

    public function test_delete_instance_removes_everything(): void {
        global $DB;
        [$course, $student, $instance] = $this->setup_activity();
        $this->getDataGenerator()->get_plugin_generator('mod_mathetrainer')
            ->create_finished_attempt($instance, (int)$student->id, 2);
        $this->assertTrue(mathetrainer_delete_instance((int)$instance->id));
        foreach (
            ['mathetrainer', 'mathetrainer_attempts', 'mathetrainer_items', 'mathetrainer_responses',
                'mathetrainer_mastery', 'mathetrainer_generation_log'] as $table
        ) {
            $this->assertSame(0, $DB->count_records($table), $table);
        }
        $this->assertFalse($DB->record_exists('grade_items', ['itemmodule' => 'mathetrainer', 'iteminstance' => $instance->id]));
    }

    public function test_course_reset(): void {
        global $DB;
        [$course, $student, $instance] = $this->setup_activity();
        $this->getDataGenerator()->get_plugin_generator('mod_mathetrainer')
            ->create_finished_attempt($instance, (int)$student->id, 2);
        $status = mathetrainer_reset_userdata((object)['courseid' => $course->id, 'reset_mathetrainer_attempts' => 1]);
        $this->assertFalse($status[0]['error']);
        $this->assertSame(0, $DB->count_records('mathetrainer_attempts'));
        $this->assertTrue($DB->record_exists('mathetrainer', ['id' => $instance->id]));
    }
}
