<?php
declare(strict_types=1);

namespace local_curriculum_math\mapper;

use moodle_database;

final class competency_query_service {
    public function __construct(private readonly moodle_database $db) {
    }

    /**
     * @return array<int, \stdClass>
     */
    public function find_by_filters(string $state, ?int $grade = null, ?string $topic = null): array {
        $sql = "SELECT c.*, f.frameworkid, f.state
                  FROM {curriculum_competency} c
                  JOIN {curriculum_framework} f ON f.id = c.frameworkfk
                 WHERE f.state = :state";
        $params = ['state' => $state];

        if ($grade !== null) {
            $sql .= ' AND (c.gradefrom IS NULL OR c.gradefrom <= :grade) AND (c.gradeto IS NULL OR c.gradeto >= :grade)';
            $params['grade'] = $grade;
        }

        if ($topic !== null && $topic !== '') {
            $sql .= ' AND c.topic = :topic';
            $params['topic'] = $topic;
        }

        $sql .= ' ORDER BY c.competencyid';

        return $this->db->get_records_sql($sql, $params);
    }
}
