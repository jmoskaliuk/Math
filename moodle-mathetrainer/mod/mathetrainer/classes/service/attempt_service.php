<?php
declare(strict_types=1);

namespace mod_mathetrainer\service;

use stdClass;

final class attempt_service {
    public function start_attempt(int $mathetrainerid, int $userid): int {
        global $DB;

        $attemptno = $DB->count_records('mathetrainer_attempts', ['mathetrainerid' => $mathetrainerid, 'userid' => $userid]) + 1;
        $record = (object)[
            'mathetrainerid' => $mathetrainerid,
            'userid' => $userid,
            'attemptno' => $attemptno,
            'status' => 'inprogress',
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        return (int)$DB->insert_record('mathetrainer_attempts', $record);
    }

    public function save_item(int $attemptid, array $task): int {
        global $DB;

        $record = new stdClass();
        $record->attemptid = $attemptid;
        $record->slot = 1;
        $record->templateid = (string)($task['templateid'] ?? 'unknown');
        $record->seed = (int)($task['seed'] ?? 0);
        $record->payloadjson = json_encode($task, JSON_UNESCAPED_UNICODE);
        $record->expectedjson = json_encode($task['expected'] ?? [], JSON_UNESCAPED_UNICODE);
        return (int)$DB->insert_record('mathetrainer_items', $record);
    }

    public function save_response(int $itemid, int $userid, array $validation, string $answer): int {
        global $DB;

        $record = new stdClass();
        $record->itemid = $itemid;
        $record->userid = $userid;
        $record->responsejson = json_encode(['value' => $answer, 'validation' => $validation], JSON_UNESCAPED_UNICODE);
        $record->iscorrect = !empty($validation['iscorrect']) ? 1 : 0;
        $record->feedback = (string)($validation['error_tag'] ?? '');
        $record->timesubmitted = time();
        return (int)$DB->insert_record('mathetrainer_responses', $record);
    }

    public function update_mastery(int $mathetrainerid, int $userid, string $competencyid, bool $iscorrect): void {
        global $DB;
        $existing = $DB->get_record('mathetrainer_mastery', [
            'mathetrainerid' => $mathetrainerid,
            'userid' => $userid,
            'competencyid' => $competencyid,
        ]);

        if (!$existing) {
            $existing = (object)[
                'mathetrainerid' => $mathetrainerid,
                'userid' => $userid,
                'competencyid' => $competencyid,
                'mastery' => 0,
                'attemptcount' => 0,
                'timemodified' => time(),
            ];
            $existing->id = $DB->insert_record('mathetrainer_mastery', $existing);
        }

        $existing->attemptcount = (int)$existing->attemptcount + 1;
        $correctcount = (float)$existing->mastery * ((int)$existing->attemptcount - 1);
        if ($iscorrect) {
            $correctcount += 1;
        }
        $existing->mastery = $correctcount / (int)$existing->attemptcount;
        $existing->timemodified = time();
        $DB->update_record('mathetrainer_mastery', $existing);
    }
}
