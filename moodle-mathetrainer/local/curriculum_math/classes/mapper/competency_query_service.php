<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Read-only competency queries for other plugins.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

declare(strict_types=1);

namespace local_curriculum_math\mapper;

use moodle_database;

/**
 * Read-only queries for other plugins (e.g. mod_mathetrainer).
 */
final class competency_query_service {
    /**
     * Creates the query service.
     *
     * @param moodle_database $db Database to query
     */
    public function __construct(
        /** @var moodle_database Database to query */
        private readonly moodle_database $db
    ) {
    }

    /**
     * Competencies of a state, optionally filtered by grade and topic, ordered by competency id.
     *
     * @param string $state State whose frameworks are searched
     * @param int|null $grade Only competencies whose grade range contains this grade
     * @param string|null $topic Only competencies with this topic
     * @return array<int, \stdClass>
     */
    public function find_by_filters(string $state, ?int $grade = null, ?string $topic = null): array {
        $sql = "SELECT c.*, f.frameworkid, f.state
                  FROM {local_curriculum_math_competency} c
                  JOIN {local_curriculum_math_framework} f ON f.id = c.frameworkfk
                 WHERE f.state = :state";
        $params = ['state' => $state];

        if ($grade !== null) {
            $sql .= ' AND (c.gradefrom IS NULL OR c.gradefrom <= :grade1) AND (c.gradeto IS NULL OR c.gradeto >= :grade2)';
            $params['grade1'] = $grade;
            $params['grade2'] = $grade;
        }

        if ($topic !== null && $topic !== '') {
            $sql .= ' AND c.topic = :topic';
            $params['topic'] = $topic;
        }

        $sql .= ' ORDER BY c.competencyid';

        return $this->db->get_records_sql($sql, $params);
    }

    /**
     * Competencies for a selection list, grouped by state: competencyid => "ID – title".
     *
     * @return array<string, array<string, string>> state => [competencyid => label]
     */
    public function competency_options(): array {
        $sql = "SELECT c.id, c.competencyid, c.title, f.state
                  FROM {local_curriculum_math_competency} c
                  JOIN {local_curriculum_math_framework} f ON f.id = c.frameworkfk
              ORDER BY f.state, c.competencyid";
        $options = [];
        foreach ($this->db->get_records_sql($sql) as $row) {
            $options[(string)$row->state][(string)$row->competencyid] = $row->competencyid . ' – ' . $row->title;
        }
        return $options;
    }

    /**
     * Exercise template ids linked to a competency (by its curriculum id).
     *
     * @return string[]
     */
    public function template_ids_for_competency(string $competencyid): array {
        $sql = "SELECT DISTINCT t.templateid
                  FROM {local_curriculum_math_template} t
                  JOIN {local_curriculum_math_templatelink} l ON l.templateid = t.id
                  JOIN {local_curriculum_math_competency} c ON c.id = l.competencyid
                 WHERE c.competencyid = :competencyid AND t.active = 1
              ORDER BY t.templateid";
        return array_values($this->db->get_fieldset_sql($sql, ['competencyid' => $competencyid]));
    }
}
