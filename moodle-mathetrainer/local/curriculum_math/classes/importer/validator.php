<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Validator for curriculum packs.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

declare(strict_types=1);

namespace local_curriculum_math\importer;

use coding_exception;

/**
 * Checks that a curriculum pack has a complete framework block.
 */
final class validator {
    /**
     * Validates a decoded pack and throws if a required framework field is missing.
     *
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
