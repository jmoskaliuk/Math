<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Addition and subtraction with rational numbers (grade 7).
 *
 * Variants: integers with signs (incl. subtracting a negative number) and
 * signed fractions. Typical misconceptions are encoded as diagnostics so that
 * the validator can name the error instead of just saying "wrong".
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\fraction;
use mod_mathetrainer\local\random;
use mod_mathetrainer\local\signed_arithmetic;

final class rational_addition implements exercise_generator {
    private const DENOMINATORS = [2, 3, 4, 5, 6, 8, 10];

    public function generate(int $seed): array {
        $rng = new random($seed);

        if ($rng->chance(2, 3)) {
            $a = new fraction($rng->nonzero_int(-20, 20));
            $b = new fraction($rng->nonzero_int(-20, 20));
            // Make sure at least one operand is negative, otherwise it is not about signs.
            if ($a->numerator > 0 && $b->numerator > 0) {
                $a = $a->negate();
            }
        } else {
            $a = self::random_fraction($rng);
            $b = self::random_fraction($rng);
            if ($a->numerator > 0 && $b->numerator > 0) {
                $b = $b->negate();
            }
        }
        $task = new signed_arithmetic($a, $b, $rng->chance(1, 2));

        return [
            'payload' => [
                'type' => 'numeric',
                'promptkey' => 'prompt_calculate',
                'expression' => $task->expression(),
            ],
            'expected' => [
                'answer' => (string)$task->result,
                'diagnostics' => $task->misconceptions(),
            ],
        ];
    }

    public static function random_fraction(random $rng): fraction {
        $denominator = $rng->pick(self::DENOMINATORS);
        $numerator = $rng->nonzero_int(-($denominator * 2 - 1), $denominator * 2 - 1);
        // Avoid fractions that reduce to integers, the task is about fractions.
        if ($numerator % $denominator === 0) {
            $numerator += $numerator > 0 ? 1 : -1;
        }
        return new fraction($numerator, $denominator);
    }
}
