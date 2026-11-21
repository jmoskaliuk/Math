<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Compare two fractions: <, = or > (grade 6).
 *
 * Diagnostics cover the two classic misconceptions:
 *  - "larger denominator means larger fraction" (1/3 > 1/2)
 *  - comparing only the numerators (3/8 > 2/3)
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\fraction;
use mod_mathetrainer\local\random;

final class fraction_compare implements exercise_generator {
    public const OPTIONS = ['<', '=', '>'];

    public function generate(int $seed): array {
        $rng = new random($seed);
        $variant = $rng->int(1, 5);

        if ($variant === 1) {
            // Equivalent fractions written differently, e.g. 2/4 and 1/2.
            $d = $rng->int(2, 6);
            $n = $rng->int(1, $d - 1);
            $k = $rng->int(2, 3);
            [$n1, $d1, $n2, $d2] = [$n, $d, $n * $k, $d * $k];
        } else if ($variant === 2) {
            // Same numerator, different denominators (unit-fraction style).
            $n1 = $n2 = $rng->int(1, 3);
            $d1 = $rng->int($n1 + 1, 12);
            do {
                $d2 = $rng->int($n1 + 1, 12);
            } while ($d2 === $d1);
        } else {
            // General proper fractions with different values.
            do {
                $d1 = $rng->int(2, 12);
                $d2 = $rng->int(2, 12);
                $n1 = $rng->int(1, $d1 - 1);
                $n2 = $rng->int(1, $d2 - 1);
            } while ($n1 * $d2 === $n2 * $d1);
        }

        if ($rng->chance(1, 2)) {
            [$n1, $d1, $n2, $d2] = [$n2, $d2, $n1, $d1];
        }

        $left = new fraction($n1, $d1);
        $right = new fraction($n2, $d2);
        $answer = self::symbol($left->compare($right));

        $diagnostics = [];
        if ($answer !== '=') {
            $reversed = $answer === '<' ? '>' : '<';
            $bydenominator = self::symbol($d1 <=> $d2);
            $bynumerator = self::symbol($n1 <=> $n2);
            if ($n1 === $n2 && $bydenominator === $reversed) {
                $diagnostics[$reversed] = 'denominator_misconception';
            } else if ($d1 !== $d2 && $bynumerator === $reversed) {
                $diagnostics[$reversed] = 'numerator_only';
            } else {
                $diagnostics[$reversed] = 'relation_reversed';
            }
            $diagnostics['='] = 'not_equal';
        } else {
            $diagnostics['<'] = 'equivalent_fractions';
            $diagnostics['>'] = 'equivalent_fractions';
        }

        return [
            'payload' => [
                'type' => 'choice',
                'promptkey' => 'prompt_compare',
                // Raw numbers are kept so that the expression is shown as written, not reduced.
                'expression' => "{$n1}/{$d1} \u{2610} {$n2}/{$d2}",
                'options' => self::OPTIONS,
            ],
            'expected' => [
                'answer' => $answer,
                'diagnostics' => $diagnostics,
            ],
        ];
    }

    private static function symbol(int $comparison): string {
        return match ($comparison) {
            -1 => '<',
            0 => '=',
            1 => '>',
        };
    }
}
