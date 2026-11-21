<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Entry point for generating one exercise from a template id and a seed.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

final class task_generator {
    /**
     * @return array{templateid: string, seed: int, payload: array<string, mixed>, expected: array<string, mixed>}
     */
    public function generate(string $templateid, int $seed): array {
        $exercise = registry::get($templateid)->generate($seed);
        return [
            'templateid' => $templateid,
            'seed' => $seed,
            'payload' => $exercise['payload'],
            'expected' => $exercise['expected'],
        ];
    }
}
