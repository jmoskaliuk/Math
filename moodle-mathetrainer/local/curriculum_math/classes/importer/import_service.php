<?php
declare(strict_types=1);

namespace local_curriculum_math\importer;

use stdClass;

final class import_service {
    public function __construct(
        private readonly pack_reader $reader,
        private readonly validator $validator,
    ) {
    }

    public function import_path(string $filepath): int {
        global $DB;

        $pack = $this->reader->read_file($filepath);
        $this->validator->validate($pack);

        $framework = $pack['framework'];

        $record = new stdClass();
        $record->frameworkid = (string)$framework['id'];
        $record->country = (string)$framework['country'];
        $record->state = (string)$framework['state'];
        $record->subject = (string)$framework['subject'];
        $record->stage = (string)$framework['stage'];
        $record->versiontag = (string)$framework['version'];
        $record->status = 'draft';
        $record->timecreated = time();

        $frameworkfk = (int)$DB->insert_record('curriculum_framework', $record);

        if (!empty($pack['competencies']) && is_array($pack['competencies'])) {
            foreach ($pack['competencies'] as $competency) {
                if (!is_array($competency) || empty($competency['id']) || empty($competency['title'])) {
                    continue;
                }
                $comp = new stdClass();
                $comp->frameworkfk = $frameworkfk;
                $comp->competencyid = (string)$competency['id'];
                $comp->title = (string)$competency['title'];
                $comp->teacherlabel = (string)($competency['teacher_label'] ?? '');
                $comp->studentlabel = (string)($competency['student_label'] ?? '');
                $comp->gradefrom = isset($competency['grade_from']) ? (int)$competency['grade_from'] : null;
                $comp->gradeto = isset($competency['grade_to']) ? (int)$competency['grade_to'] : null;
                $comp->topic = (string)($competency['topic'] ?? '');
                $comp->leitidee = (string)($competency['kmk']['leitidee'] ?? '');
                $comp->processjson = json_encode($competency['kmk']['process'] ?? [], JSON_UNESCAPED_UNICODE);
                $comp->requirementjson = json_encode($competency['kmk']['requirement_levels'] ?? [], JSON_UNESCAPED_UNICODE);
                $DB->insert_record('curriculum_competency', $comp);
            }
        }

        return $frameworkfk;
    }
}
