<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Import service for curriculum packs.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

declare(strict_types=1);

namespace local_curriculum_math\importer;

use stdClass;

/**
 * Imports a curriculum pack. Re-importing the same framework id and version
 * updates the existing records instead of failing on the unique index.
 */
final class import_service {
    /**
     * Creates the import service.
     *
     * @param pack_reader $reader Reads pack files
     * @param validator $validator Validates decoded packs
     */
    public function __construct(
        /** @var pack_reader Reads pack files */
        private readonly pack_reader $reader,
        /** @var validator Validates decoded packs */
        private readonly validator $validator,
    ) {
    }

    /**
     * Imports one pack file (framework, competencies, template links and KMK mappings) in a transaction.
     *
     * @param string $filepath Path to the pack file
     * @return int Id of the local_curriculum_math_framework record
     */
    public function import_path(string $filepath): int {
        global $DB;

        $pack = $this->reader->read_file($filepath);
        $this->validator->validate($pack);

        $framework = $pack['framework'];
        $transaction = $DB->start_delegated_transaction();

        $record = $DB->get_record('local_curriculum_math_framework', [
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
            $DB->update_record('local_curriculum_math_framework', $record);
            $frameworkfk = (int)$record->id;
        } else {
            $record->status = 'draft';
            $record->timecreated = time();
            $frameworkfk = (int)$DB->insert_record('local_curriculum_math_framework', $record);
        }

        if (!empty($pack['competencies']) && is_array($pack['competencies'])) {
            foreach ($pack['competencies'] as $competency) {
                if (!is_array($competency) || empty($competency['id']) || empty($competency['title'])) {
                    continue;
                }
                // KMK packs keep leitidee/process/requirement_levels at top level, state packs under "kmk".
                $kmk = is_array($competency['kmk'] ?? null) ? $competency['kmk'] : $competency;

                $comp = $DB->get_record('local_curriculum_math_competency', [
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
                    $DB->update_record('local_curriculum_math_competency', $comp);
                } else {
                    $comp->id = $DB->insert_record('local_curriculum_math_competency', $comp);
                }

                $generators = is_array($competency['exercise_generators'] ?? null) ? $competency['exercise_generators'] : [];
                $this->link_templates((int)$comp->id, $comp, $generators);

                if (!empty($framework['kmk_framework'])) {
                    $targets = is_array($kmk['maps_to'] ?? null) ? $kmk['maps_to'] : [];
                    $this->replace_mappings(
                        (string)$framework['id'],
                        $comp->competencyid,
                        (string)$framework['kmk_framework'],
                        $targets
                    );
                }
            }
        }

        $transaction->allow_commit();
        return $frameworkfk;
    }

    /**
     * Replaces the mappings of one state competency to KMK competencies.
     *
     * @param string $fromframework source framework
     * @param string $fromcompetency source competency id
     * @param string $toframework target framework
     * @param string[] $targets KMK competency ids
     */
    private function replace_mappings(string $fromframework, string $fromcompetency, string $toframework, array $targets): void {
        global $DB;

        $DB->delete_records('local_curriculum_math_mapping', [
            'fromframework' => $fromframework,
            'fromcompetency' => $fromcompetency,
            'toframework' => $toframework,
        ]);
        foreach (array_unique(array_map('strval', $targets)) as $target) {
            if (trim($target) === '') {
                continue;
            }
            $DB->insert_record('local_curriculum_math_mapping', (object)[
                'fromframework' => $fromframework,
                'fromcompetency' => $fromcompetency,
                'toframework' => $toframework,
                'tocompetency' => trim($target),
                'relationtype' => 'maps_to',
                'confidence' => 1.0,
            ]);
        }
    }

    /**
     * Registers the exercise templates named in a pack and links them to the competency.
     * Generator classes live in the activity plugin; only the template ids are stored here.
     *
     * @param int $competencyfk competency record id
     * @param stdClass $competency competency
     * @param string[] $templateids
     */
    private function link_templates(int $competencyfk, stdClass $competency, array $templateids): void {
        global $DB;

        foreach ($templateids as $templateid) {
            $templateid = trim((string)$templateid);
            if ($templateid === '' || !preg_match('/^[a-z0-9_]+$/', $templateid)) {
                continue;
            }
            $template = $DB->get_record('local_curriculum_math_template', ['templateid' => $templateid]);
            if (!$template) {
                $template = (object)[
                    'templateid' => $templateid,
                    'name' => $templateid,
                    'topic' => $competency->topic,
                    'gradefrom' => $competency->gradefrom,
                    'gradeto' => $competency->gradeto,
                    'generatorclass' => '',
                    'validatorclass' => '',
                    'active' => 1,
                ];
                $template->id = $DB->insert_record('local_curriculum_math_template', $template);
            }
            $link = ['templateid' => (int)$template->id, 'competencyid' => $competencyfk];
            if (!$DB->record_exists('local_curriculum_math_templatelink', $link)) {
                $DB->insert_record('local_curriculum_math_templatelink', (object)$link);
            }
        }
    }
}
