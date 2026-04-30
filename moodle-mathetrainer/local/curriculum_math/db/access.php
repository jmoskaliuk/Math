<?php

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
