<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Collects the data for the teacher report of one activity.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

use moodle_database;
use stdClass;

/**
 * Queries for the teacher report of one activity.
 */
final class report_data {
    /**
     * Creates the report data for an activity.
     *
     * @param moodle_database $db Database
     * @param stdClass $instance Activity record
     */
    public function __construct(
        /** @var moodle_database Database */
        private readonly moodle_database $db,
        /** @var stdClass Activity record */
        private readonly stdClass $instance,
    ) {
    }

    /**
     * Per learner and template: answers and correct answers.
     *
     * @return array<int, array<string, array{total: int, correct: int}>> userid => templateid => stats
     */
    public function template_stats(): array {
        $sql = 'SELECT a.userid, i.templateid, COUNT(r.id) AS total, SUM(r.iscorrect) AS correct
                  FROM {mathetrainer_responses} r
                  JOIN {mathetrainer_items} i ON i.id = r.itemid
                  JOIN {mathetrainer_attempts} a ON a.id = i.attemptid
                 WHERE a.mathetrainerid = ?
              GROUP BY a.userid, i.templateid';
        $stats = [];
        $rs = $this->db->get_recordset_sql($sql, [$this->instance->id]);
        foreach ($rs as $row) {
            $stats[(int)$row->userid][(string)$row->templateid] = [
                'total' => (int)$row->total,
                'correct' => (int)$row->correct,
            ];
        }
        $rs->close();
        return $stats;
    }

    /**
     * Per learner: error categories of wrong answers.
     *
     * @return array<int, array<string, int>> userid => category => count
     */
    public function error_stats(): array {
        $sql = 'SELECT a.userid, r.feedback, COUNT(r.id) AS cnt
                  FROM {mathetrainer_responses} r
                  JOIN {mathetrainer_items} i ON i.id = r.itemid
                  JOIN {mathetrainer_attempts} a ON a.id = i.attemptid
                 WHERE a.mathetrainerid = ? AND r.iscorrect = 0 AND r.feedback IS NOT NULL
              GROUP BY a.userid, r.feedback';
        $stats = [];
        $rs = $this->db->get_recordset_sql($sql, [$this->instance->id]);
        foreach ($rs as $row) {
            $stats[(int)$row->userid][(string)$row->feedback] = (int)$row->cnt;
        }
        $rs->close();
        foreach ($stats as &$categories) {
            arsort($categories);
        }
        return $stats;
    }

    /**
     * Per learner: finished attempts with their scores in attempt order.
     *
     * @return array<int, float[]>
     */
    public function attempt_scores(): array {
        $scores = [];
        $records = $this->db->get_records('mathetrainer_attempts', [
            'mathetrainerid' => $this->instance->id,
            'status' => attempt_manager::STATUS_FINISHED,
        ], 'userid, attemptno', 'id, userid, score, timemodified');
        foreach ($records as $attempt) {
            $scores[(int)$attempt->userid][] = (float)$attempt->score;
        }
        return $scores;
    }

    /**
     * Last activity per learner.
     *
     * @return array<int, int>
     */
    public function last_activity(): array {
        $sql = 'SELECT userid, MAX(timemodified) AS lastactivity
                  FROM {mathetrainer_attempts}
                 WHERE mathetrainerid = ? AND status = ?
              GROUP BY userid';
        $last = [];
        foreach ($this->db->get_records_sql($sql, [$this->instance->id, attempt_manager::STATUS_FINISHED]) as $row) {
            $last[(int)$row->userid] = (int)$row->lastactivity;
        }
        return $last;
    }

    /**
     * One row per learner with everything the report shows.
     *
     * @param stdClass[] $users learners (id, names) keyed by id
     * @return stdClass[]
     */
    public function rows(array $users): array {
        $templates = $this->template_stats();
        $errors = $this->error_stats();
        $scores = $this->attempt_scores();
        $last = $this->last_activity();

        $rows = [];
        foreach ($users as $userid => $user) {
            $userscores = $scores[$userid] ?? [];
            $recommendation = recommender::recommend($templates[$userid] ?? [], $errors[$userid] ?? []);
            $rows[] = (object)[
                'user' => $user,
                'attempts' => count($userscores),
                'best' => $userscores ? max($userscores) : null,
                'last' => $userscores ? end($userscores) : null,
                'grade' => grading::aggregate($userscores, (int)($this->instance->grademethod ?? grading::METHOD_HIGHEST)),
                'lastactivity' => $last[$userid] ?? null,
                'templates' => $templates[$userid] ?? [],
                'errors' => $errors[$userid] ?? [],
                'recommendation' => $recommendation,
            ];
        }
        return $rows;
    }

    /**
     * Class overview per template: answers and success rate.
     *
     * @return array<string, array{total: int, correct: int}>
     */
    public function class_template_stats(): array {
        $class = [];
        foreach ($this->template_stats() as $templates) {
            foreach ($templates as $templateid => $stats) {
                $class[$templateid]['total'] = ($class[$templateid]['total'] ?? 0) + $stats['total'];
                $class[$templateid]['correct'] = ($class[$templateid]['correct'] ?? 0) + $stats['correct'];
            }
        }
        ksort($class);
        return $class;
    }
}
