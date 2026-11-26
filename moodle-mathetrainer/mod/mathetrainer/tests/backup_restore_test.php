<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/mod/mathetrainer/lib.php');

/**
 * Backup and restore of a course with a maths trainer.
 *
 * @package    mod_mathetrainer
 * @covers     \backup_mathetrainer_activity_structure_step
 * @covers     \restore_mathetrainer_activity_structure_step
 * @license    MIT
 */
final class backup_restore_test extends \advanced_testcase {
    public function test_backup_and_restore_with_user_data(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $instance = $this->getDataGenerator()->create_module('mathetrainer', [
            'course' => $course->id, 'topic' => 'Gleichungen', 'gradelevel' => 8, 'grade' => 30,
            'grademethod' => \mod_mathetrainer\local\grading::METHOD_LAST,
        ]);
        $this->getDataGenerator()->get_plugin_generator('mod_mathetrainer')
            ->create_finished_attempt($instance, (int)$student->id, 4);

        // Backup with user data.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id
        );
        $bc->get_plan()->get_setting('users')->set_value(true);
        $bc->execute_plan();
        $file = $bc->get_results()['backup_destination'];
        $bc->destroy();

        // Restore into a new course.
        $folder = 'mathetrainer_restore_test';
        $file->extract_to_pathname(get_file_packer('application/vnd.moodle.backup'), make_backup_temp_directory($folder));
        $newcourseid = \restore_dbops::create_new_course('Restored', 'RESTORED', $course->category);
        $rc = new \restore_controller(
            $folder,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $rc->get_plan()->get_setting('users')->set_value(true);
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        $restored = $DB->get_record('mathetrainer', ['course' => $newcourseid], '*', MUST_EXIST);
        $this->assertSame('Gleichungen', $restored->topic);
        $this->assertEquals(30, $restored->grade);
        $this->assertEquals(\mod_mathetrainer\local\grading::METHOD_LAST, $restored->grademethod);

        $attempt = $DB->get_record('mathetrainer_attempts', ['mathetrainerid' => $restored->id], '*', MUST_EXIST);
        $this->assertEquals($student->id, $attempt->userid);
        $this->assertEqualsWithDelta(0.8, (float)$attempt->score, 0.0001);
        $this->assertSame(5, $DB->count_records('mathetrainer_items', ['attemptid' => $attempt->id]));
        $this->assertSame(4, (int)$DB->count_records_sql(
            'SELECT COUNT(1) FROM {mathetrainer_responses} r JOIN {mathetrainer_items} i ON i.id = r.itemid
              WHERE i.attemptid = ? AND r.iscorrect = 1',
            [$attempt->id]
        ));
        $this->assertTrue($DB->record_exists('mathetrainer_mastery', ['mathetrainerid' => $restored->id]));
    }
}
