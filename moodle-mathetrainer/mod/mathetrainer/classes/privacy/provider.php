<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Privacy API implementation for mod_mathetrainer.
 *
 * Personal data: attempts, responses and mastery values of learners.
 * Nothing is sent to external systems.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

namespace mod_mathetrainer\privacy;

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use mod_mathetrainer\local\attempt_manager;

/**
 * Privacy provider: describes, exports and deletes the attempts, responses and mastery values of users.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describes the personal data stored by the plugin.
     *
     * @param collection $collection Collection to add the metadata to
     * @return collection The updated collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('mathetrainer_attempts', [
            'userid' => 'privacy:metadata:attempts:userid',
            'attemptno' => 'privacy:metadata:attempts:attemptno',
            'status' => 'privacy:metadata:attempts:status',
            'score' => 'privacy:metadata:attempts:score',
            'timecreated' => 'privacy:metadata:attempts:timecreated',
            'timemodified' => 'privacy:metadata:attempts:timemodified',
        ], 'privacy:metadata:attempts');
        $collection->add_database_table('mathetrainer_responses', [
            'userid' => 'privacy:metadata:responses:userid',
            'responsejson' => 'privacy:metadata:responses:responsejson',
            'iscorrect' => 'privacy:metadata:responses:iscorrect',
            'feedback' => 'privacy:metadata:responses:feedback',
            'timesubmitted' => 'privacy:metadata:responses:timesubmitted',
        ], 'privacy:metadata:responses');
        $collection->add_database_table('mathetrainer_mastery', [
            'userid' => 'privacy:metadata:mastery:userid',
            'competencyid' => 'privacy:metadata:mastery:competencyid',
            'mastery' => 'privacy:metadata:mastery:mastery',
            'attemptcount' => 'privacy:metadata:mastery:attemptcount',
            'timemodified' => 'privacy:metadata:mastery:timemodified',
        ], 'privacy:metadata:mastery');
        return $collection;
    }

    /**
     * Module contexts in which the user has attempts or mastery values.
     *
     * @param int $userid Id of the user
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {mathetrainer} mt ON mt.id = cm.instance
             LEFT JOIN {mathetrainer_attempts} a ON a.mathetrainerid = mt.id AND a.userid = :userid1
             LEFT JOIN {mathetrainer_mastery} ms ON ms.mathetrainerid = mt.id AND ms.userid = :userid2
                 WHERE a.id IS NOT NULL OR ms.id IS NOT NULL";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'mathetrainer',
            'userid1' => $userid,
            'userid2' => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Adds the users with attempts or mastery values in a module context.
     *
     * @param userlist $userlist List to add the users to
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if (!$context instanceof context_module) {
            return;
        }
        $params = ['cmid' => $context->instanceid, 'modname' => 'mathetrainer'];
        $base = "FROM {course_modules} cm
                 JOIN {modules} m ON m.id = cm.module AND m.name = :modname";

        $userlist->add_from_sql('userid', "SELECT a.userid $base
                 JOIN {mathetrainer_attempts} a ON a.mathetrainerid = cm.instance
                WHERE cm.id = :cmid", $params);
        $userlist->add_from_sql('userid', "SELECT ms.userid $base
                 JOIN {mathetrainer_mastery} ms ON ms.mathetrainerid = cm.instance
                WHERE cm.id = :cmid", $params);
    }

    /**
     * Exports the attempts (with items and responses) and mastery values of the user.
     *
     * @param approved_contextlist $contextlist Approved contexts of the user
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = (int)$contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            $instanceid = self::instance_id($context);
            if ($instanceid === null) {
                continue;
            }

            $attempts = [];
            $records = $DB->get_records(
                'mathetrainer_attempts',
                ['mathetrainerid' => $instanceid, 'userid' => $userid],
                'attemptno ASC'
            );
            foreach ($records as $attempt) {
                $items = [];
                foreach ($DB->get_records('mathetrainer_items', ['attemptid' => $attempt->id], 'slot ASC') as $item) {
                    $response = $DB->get_record('mathetrainer_responses', ['itemid' => $item->id, 'userid' => $userid]);
                    $payload = json_decode($item->payloadjson, true);
                    $items[] = [
                        'slot' => (int)$item->slot,
                        'template' => $item->templateid,
                        'exercise' => (string)($payload['expression'] ?? ''),
                        'response' => $response ? (json_decode($response->responsejson, true)['answer'] ?? '') : null,
                        'correct' => $response ? transform::yesno((bool)$response->iscorrect) : null,
                        'errorcategory' => $response ? $response->feedback : null,
                        'timesubmitted' => $response ? transform::datetime($response->timesubmitted) : null,
                    ];
                }
                $attempts[] = [
                    'attemptno' => (int)$attempt->attemptno,
                    'status' => $attempt->status,
                    'score' => $attempt->score,
                    'timecreated' => transform::datetime($attempt->timecreated),
                    'timemodified' => transform::datetime($attempt->timemodified),
                    'items' => $items,
                ];
            }

            $mastery = [];
            foreach ($DB->get_records('mathetrainer_mastery', ['mathetrainerid' => $instanceid, 'userid' => $userid]) as $m) {
                $mastery[] = [
                    'competency' => $m->competencyid,
                    'mastery' => $m->mastery,
                    'attemptcount' => (int)$m->attemptcount,
                    'timemodified' => transform::datetime($m->timemodified),
                ];
            }

            $data = helper::get_context_data($context, $contextlist->get_user());
            $data->attempts = $attempts;
            $data->mastery = $mastery;
            writer::with_context($context)->export_data([], $data);
            helper::export_context_files($context, $contextlist->get_user());
        }
    }

    /**
     * Deletes the data of all users in a module context.
     *
     * @param context $context Context to delete the data in
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;
        if ($instanceid = self::instance_id($context)) {
            attempt_manager::delete_instance_data($DB, $instanceid);
        }
    }

    /**
     * Deletes the data of one user in the approved contexts.
     *
     * @param approved_contextlist $contextlist Approved contexts of the user
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = (int)$contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($instanceid = self::instance_id($context)) {
                attempt_manager::delete_instance_data($DB, $instanceid, $userid);
            }
        }
    }

    /**
     * Deletes the data of the approved users in a module context.
     *
     * @param approved_userlist $userlist Approved users and their context
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        if (!$instanceid = self::instance_id($userlist->get_context())) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            attempt_manager::delete_instance_data($DB, $instanceid, (int)$userid);
        }
    }

    /**
     * Activity instance id for a module context of this plugin, otherwise null.
     */
    private static function instance_id(context $context): ?int {
        if (!$context instanceof context_module) {
            return null;
        }
        $cm = get_coursemodule_from_id('mathetrainer', $context->instanceid);
        return $cm ? (int)$cm->instance : null;
    }
}
