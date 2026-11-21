<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Solve simple linear equations a·x + b = c (grade 7-8, AFB I/II).
 *
 * Variants: x + b = c (one step) and a·x ± b = c (two steps), always with an
 * integer solution. Diagnostics:
 *  - inverse_operation:  b moved to the other side without changing the sign
 *  - forgot_divide:      only b eliminated, division by a forgotten
 *  - multiplied_instead: multiplied by a instead of dividing
 *  - sign_error
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\fraction;
use mod_mathetrainer\local\random;

final class linear_equation implements exercise_generator {
    public function generate(int $seed): array {
        $rng = new random($seed);

        $x = $rng->nonzero_int(-10, 10);
        $a = $rng->chance(1, 4) ? 1 : $rng->int(2, 9) * ($rng->chance(1, 4) ? -1 : 1);
        $b = $rng->nonzero_int(-20, 20);
        $c = $a * $x + $b;

        $solution = new fraction($x);
        $diagnostics = [];
        $add = static function (fraction $wrong, string $category) use (&$diagnostics, $solution): void {
            if (!$wrong->equals($solution) && !isset($diagnostics[(string)$wrong])) {
                $diagnostics[(string)$wrong] = $category;
            }
        };
        $add(new fraction($c + $b, $a), 'inverse_operation');
        if ($a !== 1) {
            $add(new fraction($c - $b), 'forgot_divide');
            $add(new fraction(($c - $b) * $a), 'multiplied_instead');
        }
        $add($solution->negate(), 'sign_error');

        $term = match ($a) {
            1 => 'x',
            -1 => "\u{2212}x",
            default => str_replace('-', "\u{2212}", (string)$a) . 'x',
        };
        $expression = $term . ($b < 0 ? " \u{2212} " : ' + ') . abs($b) . ' = ' . str_replace('-', "\u{2212}", (string)$c);

        return [
            'payload' => [
                'type' => 'numeric',
                'promptkey' => 'prompt_equation',
                'expression' => $expression,
            ],
            'expected' => [
                'answer' => (string)$solution,
                'diagnostics' => $diagnostics,
            ],
        ];
    }
}
