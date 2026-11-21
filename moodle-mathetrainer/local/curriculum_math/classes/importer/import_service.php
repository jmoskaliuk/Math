<?php
declare(strict_types=1);

namespace local_curriculum_math\importer;

use stdClass;

/**
 * Imports a curriculum pack. Re-importing the same framework id and version
 * updates the existing records instead of failing on the unique index.
 */
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
        $transaction = $DB->start_delegated_transaction();

        $record = $DB->get_record('curriculum_framework', [
            'frameworkid' => (string)$framework['id'],
            'versiontag' => (string)$framework['version'],
        ]) ?: new stdClass();
        $record->frameworkid = (string)$framework['id'];
        $record->country = (string)$framework['country'];
        $record->state = (string)$framework['state'];
        $record->subject = (string)$framework['subject'];
        $record->stage = (string)$framework['stage'];
        $record->versiontag = (string)$framework['version'];

        if (!empty($record->id)) {
            $DB->update_record('curriculum_framework', $record);
            $frameworkfk = (int)$record->id;
        } else {
            $record->status = 'draft';
            $record->timecreated = time();
            $frameworkfk = (int)$DB->insert_record('curriculum_framework', $record);
        }

        if (!empty($pack['competencies']) && is_array($pack['competencies'])) {
            foreach ($pack['competencies'] as $competency) {
                if (!is_array($competency) || empty($competency['id']) || empty($competency['title'])) {
                    continue;
                }
                // KMK packs keep leitidee/process/requirement_levels at top level, state packs under "kmk".
                $kmk = is_array($competency['kmk'] ?? null) ? $competency['kmk'] : $competency;

                $comp = $DB->get_record('curriculum_competency', [
                    'frameworkfk' => $frameworkfk,
                    'competencyid' => (string)$competency['id'],
                ]) ?: new stdClass();
                $comp->frameworkfk = $frameworkfk;
                $comp->competencyid = (string)$competency['id'];
                $comp->title = (string)$competency['title'];
                $comp->teacherlabel = (string)($competency['teacher_label'] ?? '');
                $comp->studentlabel = (string)($competency['student_label'] ?? '');
                $comp->gradefrom = isset($competency['grade_from']) ? (int)$competency['grade_from'] : null;
                $comp->gradeto = isset($competency['grade_to']) ? (int)$competency['grade_to'] : null;
                $comp->topic = (string)($competency['topic'] ?? '');
                $comp->leitidee = (string)($kmk['leitidee'] ?? '');
                $comp->processjson = json_encode($kmk['process'] ?? [], JSON_UNESCAPED_UNICODE);
                $comp->requirementjson = json_encode($kmk['requirement_levels'] ?? [], JSON_UNESCAPED_UNICODE);

                if (!empty($comp->id)) {
                    $DB->update_record('curriculum_competency', $comp);
                } else {
                    $DB->insert_record('curriculum_competency', $comp);
                }
            }
        }

        $transaction->allow_commit();
        return $frameworkfk;
    }
}
