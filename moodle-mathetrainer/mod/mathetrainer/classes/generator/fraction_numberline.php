<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Read off a fraction marked on a number line from 0 to 1 or 0 to 2
 * (grade 5-6, AFB I).
 *
 * Diagnostics:
 *  - counted_marks: denominator = number of tick marks instead of parts per unit
 *  - unit_error:    the whole line (0..2) taken as one unit
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\fraction;
use mod_mathetrainer\local\random;

/**
 * Generator for reading off a fraction on a number line.
 */
final class fraction_numberline implements exercise_generator {
    /**
     * Generates a number line with a marked fraction.
     *
     * @param int $seed Seed for the random number generator
     * @return array Exercise with payload and expected answer
     */
    public function generate(int $seed): array {
        $rng = new random($seed);
        $parts = $rng->pick([2, 3, 4, 5, 6, 8, 10]);
        $units = $rng->chance(1, 3) ? 2 : 1;
        $divisions = $parts * $units;

        do {
            $marked = $rng->int(1, $divisions - 1);
        } while ($marked % $parts === 0);

        $answer = new fraction($marked, $parts);
        $labels = [0 => '0'];
        for ($u = 1; $u <= $units; $u++) {
            $labels[$u * $parts] = (string)$u;
        }

        $diagnostics = [];
        $add = static function (fraction $wrong, string $category) use (&$diagnostics, $answer): void {
            if (!$wrong->equals($answer) && !isset($diagnostics[(string)$wrong])) {
                $diagnostics[(string)$wrong] = $category;
            }
        };
        $add(new fraction($marked, $parts + 1), 'counted_marks');
        if ($units === 2) {
            $add(new fraction($marked, $divisions), 'unit_error');
        }

        return [
            'payload' => [
                'type' => 'numeric',
                'promptkey' => 'prompt_fraction_numberline',
                'expression' => '',
                'numberline' => [
                    'divisions' => $divisions,
                    'labels' => $labels,
                    'marked' => $marked,
                ],
            ],
            'expected' => [
                'answer' => (string)$answer,
                'diagnostics' => $diagnostics,
            ],
        ];
    }
}
