<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Creates attempts with generated items, grades submissions and keeps
 * the per-competency mastery values up to date.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

use mod_mathetrainer\generator\registry;
use mod_mathetrainer\generator\task_generator;
use mod_mathetrainer\validator\answer_validator;
use moodle_database;
use stdClass;

final class attempt_manager {
    public const ITEMS_PER_ATTEMPT = 5;

    public const STATUS_INPROGRESS = 'inprogress';
    public const STATUS_FINISHED = 'finished';

    public function __construct(
        private readonly moodle_database $db,
        private readonly stdClass $instance,
    ) {
    }

    public function get_inprogress_attempt(int $userid): ?stdClass {
        $records = $this->db->get_records('mathetrainer_attempts', [
            'mathetrainerid' => $this->instance->id,
            'userid' => $userid,
            'status' => self::STATUS_INPROGRESS,
        ], 'id DESC', '*', 0, 1);
        return $records ? reset($records) : null;
    }

    public function get_attempt(int $attemptid, int $userid): ?stdClass {
        return $this->db->get_record('mathetrainer_attempts', [
            'id' => $attemptid,
            'mathetrainerid' => $this->instance->id,
            'userid' => $userid,
        ]) ?: null;
    }

    /**
     * @return stdClass[] finished attempts of the user, newest first
     */
    public function get_finished_attempts(int $userid): array {
        return $this->db->get_records('mathetrainer_attempts', [
            'mathetrainerid' => $this->instance->id,
            'userid' => $userid,
            'status' => self::STATUS_FINISHED,
        ], 'attemptno DESC');
    }

    /**
     * Returns the running attempt or creates a new one with freshly generated items.
     */
    public function start_attempt(int $userid): stdClass {
        if ($existing = $this->get_inprogress_attempt($userid)) {
            return $existing;
        }

        $transaction = $this->db->start_delegated_transaction();
        $now = time();

        $attemptno = (int)$this->db->get_field_sql(
            'SELECT COALESCE(MAX(attemptno), 0) FROM {mathetrainer_attempts} WHERE mathetrainerid = ? AND userid = ?',
            [$this->instance->id, $userid]
        ) + 1;

        $attempt = (object)[
            'mathetrainerid' => $this->instance->id,
            'userid' => $userid,
            'attemptno' => $attemptno,
            'status' => self::STATUS_INPROGRESS,
            'score' => null,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $attempt->id = $this->db->insert_record('mathetrainer_attempts', $attempt);

        $templates = $this->select_templates();
        $generator = new task_generator();

        for ($slot = 1; $slot <= self::ITEMS_PER_ATTEMPT; $slot++) {
            $templateid = $templates[($slot - 1) % count($templates)];
            $seed = random_int(1, 2147483647);
            $exercise = $generator->generate($templateid, $seed);

            $this->db->insert_record('mathetrainer_items', (object)[
                'attemptid' => $attempt->id,
                'slot' => $slot,
                'templateid' => $templateid,
                'seed' => $seed,
                'payloadjson' => json_encode($exercise['payload'], JSON_UNESCAPED_UNICODE),
                'expectedjson' => json_encode($exercise['expected'], JSON_UNESCAPED_UNICODE),
            ]);
            $this->db->insert_record('mathetrainer_generation_log', (object)[
                'mathetrainerid' => $this->instance->id,
                'attemptid' => $attempt->id,
                'templateid' => $templateid,
                'seed' => $seed,
                'status' => 'ok',
                'message' => null,
                'timecreated' => $now,
            ]);
        }

        $transaction->allow_commit();
        return $attempt;
    }

    /**
     * Template ids for this activity: from the curriculum competency if one is
     * configured and linked to exercises, otherwise from topic/grade/AFB.
     *
     * @return string[]
     */
    public function select_templates(): array {
        return registry::find(
            $this->instance->topic ?? null,
            isset($this->instance->gradelevel) ? (int)$this->instance->gradelevel : null,
            $this->instance->requirementlevel ?? null,
            $this->curriculum_templates()
        );
    }

    /**
     * Template ids linked to the configured competency in local_curriculum_math,
     * or null if no competency is set or the curriculum plugin is not installed.
     *
     * @return string[]|null
     */
    private function curriculum_templates(): ?array {
        $competencyid = (string)($this->instance->competencyid ?? '');
        if ($competencyid === '' || !class_exists(\local_curriculum_math\mapper\competency_query_service::class)) {
            return null;
        }
        $ids = (new \local_curriculum_math\mapper\competency_query_service($this->db))
            ->template_ids_for_competency($competencyid);
        return $ids ?: null;
    }

    /**
     * @return stdClass[] items of an attempt keyed by slot, with decoded payload/expected
     */
    public function get_items(int $attemptid): array {
        $items = [];
        foreach ($this->db->get_records('mathetrainer_items', ['attemptid' => $attemptid], 'slot ASC') as $item) {
            $item->payload = json_decode($item->payloadjson, true);
            $item->expected = json_decode($item->expectedjson, true);
            $items[(int)$item->slot] = $item;
        }
        return $items;
    }

    /**
     * @return array<int, stdClass> responses of an attempt keyed by slot
     */
    public function get_responses(int $attemptid): array {
        $sql = 'SELECT i.slot, r.*
                  FROM {mathetrainer_responses} r
                  JOIN {mathetrainer_items} i ON i.id = r.itemid
                 WHERE i.attemptid = ?
              ORDER BY i.slot';
        $responses = [];
        foreach ($this->db->get_records_sql($sql, [$attemptid]) as $response) {
            $response->result = json_decode($response->responsejson, true);
            $responses[(int)$response->slot] = $response;
        }
        return $responses;
    }

    /**
     * Grades all items of an in-progress attempt and finishes it.
     *
     * @param array<int, string> $answers learner answers keyed by slot
     */
    public function submit(stdClass $attempt, array $answers): stdClass {
        if ($attempt->status !== self::STATUS_INPROGRESS) {
            throw new \moodle_exception('attemptalreadyfinished', 'mathetrainer');
        }

        $validator = new answer_validator();
        $items = $this->get_items((int)$attempt->id);
        $now = time();
        $correctcount = 0;

        $transaction = $this->db->start_delegated_transaction();
        foreach ($items as $slot => $item) {
            $answer = (string)($answers[$slot] ?? '');
            $result = $validator->check($item->payload, $item->expected, $answer);
            $correctcount += $result['correct'] ? 1 : 0;

            $this->db->insert_record('mathetrainer_responses', (object)[
                'itemid' => $item->id,
                'userid' => $attempt->userid,
                'responsejson' => json_encode(['answer' => $answer] + $result, JSON_UNESCAPED_UNICODE),
                'iscorrect' => $result['correct'] ? 1 : 0,
                // Category key; the readable text is resolved at display time.
                'feedback' => $result['category'],
                'timesubmitted' => $now,
            ]);
        }

        $attempt->status = self::STATUS_FINISHED;
        $attempt->score = $items ? round($correctcount / count($items), 5) : 0;
        $attempt->timemodified = $now;
        $this->db->update_record('mathetrainer_attempts', $attempt);

        $this->update_mastery((int)$attempt->userid, (float)$attempt->score, $now);
        $transaction->allow_commit();

        return $attempt;
    }

    /**
     * Competency key used for mastery tracking: the configured competency,
     * otherwise the topic of the activity.
     */
    public function competency_key(): string {
        if (!empty($this->instance->competencyid)) {
            return (string)$this->instance->competencyid;
        }
        return 'topic:' . ($this->instance->topic ?: 'all');
    }

    /**
     * Running mean of attempt scores per learner and competency.
     */
    private function update_mastery(int $userid, float $score, int $now): void {
        $key = $this->competency_key();
        $record = $this->db->get_record('mathetrainer_mastery', [
            'mathetrainerid' => $this->instance->id,
            'userid' => $userid,
            'competencyid' => $key,
        ]);
        if ($record) {
            $count = (int)$record->attemptcount;
            $record->mastery = round(((float)$record->mastery * $count + $score) / ($count + 1), 5);
            $record->attemptcount = $count + 1;
            $record->timemodified = $now;
            $this->db->update_record('mathetrainer_mastery', $record);
            return;
        }
        $this->db->insert_record('mathetrainer_mastery', (object)[
            'mathetrainerid' => $this->instance->id,
            'userid' => $userid,
            'competencyid' => $key,
            'mastery' => round($score, 5),
            'attemptcount' => 1,
            'timemodified' => $now,
        ]);
    }

    /**
     * Overview for teachers: one row per learner with attempts, last and mean score.
     *
     * @return stdClass[]
     */
    public function get_report_rows(): array {
        $sql = 'SELECT a.userid, COUNT(a.id) AS attempts, AVG(a.score) AS avgscore,
                       MAX(a.timemodified) AS lastattempt
                  FROM {mathetrainer_attempts} a
                 WHERE a.mathetrainerid = ? AND a.status = ?
              GROUP BY a.userid';
        return $this->db->get_records_sql($sql, [$this->instance->id, self::STATUS_FINISHED]);
    }

    /**
     * Most frequent error categories across all responses of this activity.
     *
     * @return array<string, int> category => count, descending
     */
    public function get_error_statistics(): array {
        $sql = 'SELECT r.feedback, COUNT(r.id) AS cnt
                  FROM {mathetrainer_responses} r
                  JOIN {mathetrainer_items} i ON i.id = r.itemid
                  JOIN {mathetrainer_attempts} a ON a.id = i.attemptid
                 WHERE a.mathetrainerid = ? AND r.iscorrect = 0 AND r.feedback IS NOT NULL
              GROUP BY r.feedback';
        $stats = [];
        foreach ($this->db->get_records_sql($sql, [$this->instance->id]) as $row) {
            $stats[(string)$row->feedback] = (int)$row->cnt;
        }
        arsort($stats);
        return $stats;
    }

    /**
     * Removes all learner data of one activity (used on delete and reset).
     */
    public static function delete_instance_data(moodle_database $db, int $instanceid, ?int $userid = null): void {
        $params = ['mathetrainerid' => $instanceid];
        if ($userid !== null) {
            $params['userid'] = $userid;
        }
        $attemptids = $db->get_fieldset_select(
            'mathetrainer_attempts',
            'id',
            'mathetrainerid = :mathetrainerid' . ($userid !== null ? ' AND userid = :userid' : ''),
            $params
        );
        if ($attemptids) {
            [$insql, $inparams] = $db->get_in_or_equal($attemptids);
            $itemids = $db->get_fieldset_select('mathetrainer_items', 'id', "attemptid $insql", $inparams);
            if ($itemids) {
                [$itemsql, $itemparams] = $db->get_in_or_equal($itemids);
                $db->delete_records_select('mathetrainer_responses', "itemid $itemsql", $itemparams);
            }
            $db->delete_records_select('mathetrainer_items', "attemptid $insql", $inparams);
            $db->delete_records_select('mathetrainer_generation_log', "attemptid $insql", $inparams);
            $db->delete_records_select('mathetrainer_attempts', "id $insql", $inparams);
        }
        $db->delete_records('mathetrainer_mastery', $params);
        if ($userid === null) {
            $db->delete_records('mathetrainer_generation_log', ['mathetrainerid' => $instanceid]);
        }
    }
}
