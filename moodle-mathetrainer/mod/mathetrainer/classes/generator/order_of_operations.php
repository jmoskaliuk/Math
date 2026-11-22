<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Order of operations with natural numbers (grade 5, AFB I/II):
 * "Punkt vor Strich" and brackets.
 *
 * All intermediate and final results are natural numbers. Diagnostics:
 *  - order_of_operations: calculated strictly from left to right
 *  - ignored_brackets:    brackets ignored, "Punkt vor Strich" applied
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\random;

final class order_of_operations implements exercise_generator {
    private const TIMES = "\u{00B7}";
    private const MINUS = "\u{2212}";

    public function generate(int $seed): array {
        $rng = new random($seed);
        $diagnostics = [];

        switch ($rng->int(1, 5)) {
            case 1:
                // a + b · c
                [$a, $b, $c] = [$rng->int(2, 30), $rng->int(2, 9), $rng->int(2, 9)];
                $expression = "$a + $b " . self::TIMES . " $c";
                $answer = $a + $b * $c;
                $diagnostics[(string)(($a + $b) * $c)] = 'order_of_operations';
                break;
            case 2:
                // a − b · c with a > b · c
                [$b, $c] = [$rng->int(2, 9), $rng->int(2, 9)];
                $a = $b * $c + $rng->int(1, 40);
                $expression = "$a " . self::MINUS . " $b " . self::TIMES . " $c";
                $answer = $a - $b * $c;
                $diagnostics[(string)(($a - $b) * $c)] = 'order_of_operations';
                break;
            case 3:
                // a + b : c with c | b
                $c = $rng->int(2, 9);
                $b = $c * $rng->int(2, 9);
                $a = $rng->int(2, 30);
                $expression = "$a + $b : $c";
                $answer = $a + intdiv($b, $c);
                if (($a + $b) % $c === 0) {
                    $diagnostics[(string)intdiv($a + $b, $c)] = 'order_of_operations';
                }
                break;
            case 4:
                // (a + b) · c
                [$a, $b, $c] = [$rng->int(2, 20), $rng->int(2, 20), $rng->int(2, 9)];
                $expression = "($a + $b) " . self::TIMES . " $c";
                $answer = ($a + $b) * $c;
                $diagnostics[(string)($a + $b * $c)] = 'ignored_brackets';
                break;
            default:
                // a · (b − c) with b > c
                $a = $rng->int(2, 9);
                $c = $rng->int(1, 15);
                $b = $c + $rng->int(1, 15);
                $expression = "$a " . self::TIMES . " ($b " . self::MINUS . " $c)";
                $answer = $a * ($b - $c);
                $wrong = $a * $b - $c;
                $diagnostics[(string)$wrong] = 'ignored_brackets';
                break;
        }

        unset($diagnostics[(string)$answer]);

        return [
            'payload' => [
                'type' => 'numeric',
                'promptkey' => 'prompt_calculate',
                'expression' => $expression,
            ],
            'expected' => [
                'answer' => (string)$answer,
                'diagnostics' => $diagnostics,
            ],
        ];
    }
}
