<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Find and correct the mistake in a worked calculation with signed numbers
 * (grade 7, AFB II/III).
 *
 * A calculation with a typical mistake is shown; the learner enters the
 * correct result. Copying the wrong result is diagnosed separately.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\fraction;
use mod_mathetrainer\local\random;
use mod_mathetrainer\local\signed_arithmetic;

/**
 * Generator for finding and correcting a sign error in a worked calculation.
 */
final class sign_error_analysis implements exercise_generator {
    /**
     * Generates a signed calculation shown with a typical wrong result.
     *
     * @param int $seed Seed for the random number generator
     * @return array Exercise with payload and expected answer
     */
    public function generate(int $seed): array {
        $rng = new random($seed);

        // Retry until the drawn task has at least one typical mistake to show.
        do {
            $a = new fraction($rng->nonzero_int(-15, 15));
            $b = new fraction($rng->nonzero_int(-15, 15));
            // The exercise is about signs: at least one operand must be negative.
            if ($a->numerator > 0 && $b->numerator > 0) {
                $b = $b->negate();
            }
            $task = new signed_arithmetic($a, $b, $rng->chance(1, 2));
            $misconceptions = $task->misconceptions();
        } while (!$misconceptions);

        // Prefer the didactically interesting mistakes over a plain sign error.
        $specific = array_filter($misconceptions, static fn(string $c): bool => $c !== 'sign_error');
        $shown = (string)$rng->pick(array_keys($specific ?: $misconceptions));

        $diagnostics = [$shown => 'copied_wrong_result'] + $misconceptions;

        return [
            'payload' => [
                'type' => 'numeric',
                'promptkey' => 'prompt_find_error',
                'expression' => $task->expression() . ' = ' . str_replace('-', "\u{2212}", $shown),
            ],
            'expected' => [
                'answer' => (string)$task->result,
                'diagnostics' => $diagnostics,
            ],
        ];
    }
}
