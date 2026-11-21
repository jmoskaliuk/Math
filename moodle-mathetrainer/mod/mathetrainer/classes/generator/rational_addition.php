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
            $a = $this->random_fraction($rng);
            $b = $this->random_fraction($rng);
            if ($a->numerator > 0 && $b->numerator > 0) {
                $b = $b->negate();
            }
        }
        $subtract = $rng->chance(1, 2);

        $result = $subtract ? $a->subtract($b) : $a->add($b);
        $diagnostics = [];

        // "Minus minus" treated as minus: a - (-b) computed as a - |b|.
        if ($subtract && $b->numerator < 0) {
            $this->add_diagnostic($diagnostics, $result, $a->add($b), 'double_negative');
        }

        // Effective addition a + c, c = ±b. Magnitudes added although signs differ.
        $c = $subtract ? $b->negate() : $b;
        if (($a->numerator < 0) !== ($c->numerator < 0)) {
            $magnitude = $a->abs()->add($c->abs());
            $larger = $a->abs()->compare($c->abs()) >= 0 ? $a : $c;
            $this->add_diagnostic(
                $diagnostics,
                $result,
                $larger->numerator < 0 ? $magnitude->negate() : $magnitude,
                'magnitude_error'
            );
        }

        // Fractions: numerators and denominators added separately.
        if (!$a->is_integer() || !$b->is_integer()) {
            $cn = $c->numerator;
            $wrong = new fraction($a->numerator + $cn, $a->denominator + $c->denominator);
            $this->add_diagnostic($diagnostics, $result, $wrong, 'added_denominators');
        }

        if (!$result->is_zero()) {
            $this->add_diagnostic($diagnostics, $result, $result->negate(), 'sign_error');
        }

        $expression = $this->format_operand($a, false)
            . ($subtract ? " \u{2212} " : ' + ')
            . $this->format_operand($b, true);

        return [
            'payload' => [
                'type' => 'numeric',
                'promptkey' => 'prompt_calculate',
                'expression' => $expression,
            ],
            'expected' => [
                'answer' => (string)$result,
                'diagnostics' => $diagnostics,
            ],
        ];
    }

    private function random_fraction(random $rng): fraction {
        $denominator = $rng->pick(self::DENOMINATORS);
        $numerator = $rng->nonzero_int(-($denominator * 2 - 1), $denominator * 2 - 1);
        // Avoid fractions that reduce to integers, the task is about fractions.
        if ($numerator % $denominator === 0) {
            $numerator += $numerator > 0 ? 1 : -1;
        }
        return new fraction($numerator, $denominator);
    }

    /**
     * @param array<string, string> $diagnostics
     */
    private function add_diagnostic(array &$diagnostics, fraction $correct, fraction $wrong, string $category): void {
        if ($wrong->equals($correct)) {
            return;
        }
        $key = (string)$wrong;
        if (!isset($diagnostics[$key])) {
            $diagnostics[$key] = $category;
        }
    }

    private function format_operand(fraction $value, bool $bracketnegative): string {
        $text = $value->display();
        return ($bracketnegative && $value->numerator < 0) ? '(' . $text . ')' : $text;
    }
}
