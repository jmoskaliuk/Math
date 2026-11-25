<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;
use mod_mathetrainer\privacy\provider;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/mathetrainer/lib.php');

/**
 * Privacy API tests.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\privacy\provider
 * @license    MIT
 */
final class privacy_provider_test extends provider_testcase {
    /**
     * Creates a course with an activity and finished attempts of two students.
     *
     * @return array [context, student1, student2, instance]
     */
    private function setup_data(): array {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student1 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $student2 = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $instance = $this->getDataGenerator()->create_module('mathetrainer', ['course' => $course->id]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_mathetrainer');
        $generator->create_finished_attempt($instance, (int)$student1->id, 3);
        $generator->create_finished_attempt($instance, (int)$student2->id, 1);
        $context = \context_module::instance($instance->cmid);
        return [$context, $student1, $student2, $instance];
    }

    public function test_contexts_and_users(): void {
        [$context, $student1, $student2] = $this->setup_data();
        $this->assertSame([$context->id], array_map('intval', provider::get_contexts_for_userid($student1->id)->get_contextids()));

        $userlist = new userlist($context, 'mod_mathetrainer');
        provider::get_users_in_context($userlist);
        $this->assertEqualsCanonicalizing([$student1->id, $student2->id], $userlist->get_userids());
    }

    public function test_export(): void {
        [$context, $student1] = $this->setup_data();
        $this->export_context_data_for_user($student1->id, $context, 'mod_mathetrainer');
        $data = writer::with_context($context)->get_data([]);
        $this->assertCount(1, $data->attempts);
        $this->assertCount(5, $data->attempts[0]['items']);
        $this->assertCount(1, $data->mastery);
    }

    public function test_delete_for_user(): void {
        global $DB;
        [$context, $student1, $student2] = $this->setup_data();
        provider::delete_data_for_user(new approved_contextlist($student1, 'mod_mathetrainer', [$context->id]));
        $this->assertSame(0, $DB->count_records('mathetrainer_attempts', ['userid' => $student1->id]));
        $this->assertSame(0, $DB->count_records('mathetrainer_responses', ['userid' => $student1->id]));
        $this->assertSame(0, $DB->count_records('mathetrainer_mastery', ['userid' => $student1->id]));
        $this->assertSame(1, $DB->count_records('mathetrainer_attempts', ['userid' => $student2->id]));
        $this->assertSame(5, $DB->count_records('mathetrainer_responses', ['userid' => $student2->id]));
    }

    public function test_delete_for_users_and_context(): void {
        global $DB;
        [$context, $student1, $student2] = $this->setup_data();
        provider::delete_data_for_users(new approved_userlist($context, 'mod_mathetrainer', [$student2->id]));
        $this->assertSame(0, $DB->count_records('mathetrainer_attempts', ['userid' => $student2->id]));
        $this->assertSame(1, $DB->count_records('mathetrainer_attempts', ['userid' => $student1->id]));

        provider::delete_data_for_all_users_in_context($context);
        $this->assertSame(0, $DB->count_records('mathetrainer_attempts'));
        $this->assertSame(0, $DB->count_records('mathetrainer_items'));
        $this->assertSame(0, $DB->count_records('mathetrainer_mastery'));
    }
}
