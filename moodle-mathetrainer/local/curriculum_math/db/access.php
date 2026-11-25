<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Capability definitions for local_curriculum_math.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = [
    'local/curriculum_math:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
