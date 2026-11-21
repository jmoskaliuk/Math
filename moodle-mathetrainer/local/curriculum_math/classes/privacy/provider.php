<?php
declare(strict_types=1);

namespace local_curriculum_math\privacy;

/**
 * The plugin stores curriculum data only, no personal data.
 */
final class provider implements \core_privacy\local\metadata\null_provider {
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
