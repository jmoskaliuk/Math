<?php

defined('MOODLE_INTERNAL') || die();

function mathetrainer_supports(string $feature): bool|string|null {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_BACKUP_MOODLE2 => true,
        default => null,
    };
}
