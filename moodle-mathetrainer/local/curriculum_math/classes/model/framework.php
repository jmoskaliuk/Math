<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Value object for a curriculum framework.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

declare(strict_types=1);

namespace local_curriculum_math\model;

/**
 * Immutable description of a curriculum framework (id, state, stage and version).
 */
final class framework {
    /**
     * Creates the framework value object.
     *
     * @param string $id Framework id
     * @param string $state State the framework belongs to
     * @param string $stage School stage
     * @param string $version Version tag
     */
    public function __construct(
        /** @var string Framework id */
        public readonly string $id,
        /** @var string State the framework belongs to */
        public readonly string $state,
        /** @var string School stage */
        public readonly string $stage,
        /** @var string Version tag */
        public readonly string $version,
    ) {
    }
}
