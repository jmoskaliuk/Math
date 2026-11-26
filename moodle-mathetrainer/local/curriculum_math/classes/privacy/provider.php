<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Privacy provider for local_curriculum_math.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

declare(strict_types=1);

namespace local_curriculum_math\privacy;

/**
 * The plugin stores curriculum data only, no personal data.
 */
final class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Returns the language string id explaining why no personal data is stored.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
