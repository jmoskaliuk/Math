<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Contract for deterministic exercise generators.
 *
 * Generators must be pure: no database, no globals, no language strings.
 * The same seed must always produce the same exercise.
 *
 * Returned structure:
 *  - payload:  what the learner sees
 *      type:       'numeric' | 'choice'
 *      promptkey:  language string key in mod_mathetrainer
 *      expression: text inserted into the prompt string
 *      options:    (choice only) list of allowed answers
 *  - expected: never shown to the learner before submission
 *      answer:      canonical correct answer
 *      diagnostics: map canonical wrong answer => error category
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

interface exercise_generator {
    /**
     * @return array{payload: array<string, mixed>, expected: array{answer: string, diagnostics: array<string, string>}}
     */
    public function generate(int $seed): array;
}
