<?php
declare(strict_types=1);

namespace local_curriculum_math\importer;

use coding_exception;

final class validator {
    /**
     * @param array<string, mixed> $pack
     */
    public function validate(array $pack): void {
        if (empty($pack['framework']) || !is_array($pack['framework'])) {
            throw new coding_exception('Missing framework block.');
        }

        $required = ['id', 'country', 'state', 'subject', 'stage', 'version'];
        foreach ($required as $field) {
            if (empty($pack['framework'][$field])) {
                throw new coding_exception('Missing framework field: ' . $field);
            }
        }
    }
}
