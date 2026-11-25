<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Signed addition/subtraction a ± b with the typical wrong results learners produce.
 *
 * Shared by rational_addition (learner computes) and sign_error_analysis
 * (learner corrects a wrong calculation).
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

/**
 * A signed addition or subtraction a ± b with its result and typical wrong results.
 */
final class signed_arithmetic {
    /** @var fraction Correct result */
    public readonly fraction $result;

    /**
     * Creates the task and computes its result.
     *
     * @param fraction $a First operand
     * @param fraction $b Second operand
     * @param bool $subtract True for a - b, false for a + b
     */
    public function __construct(
        /** @var fraction First operand */
        public readonly fraction $a,
        /** @var fraction Second operand */
        public readonly fraction $b,
        /** @var bool True for a - b, false for a + b */
        public readonly bool $subtract,
    ) {
        $this->result = $subtract ? $a->subtract($b) : $a->add($b);
    }

    /**
     * Typical wrong results, most specific first.
     *
     * @return array<string, string> canonical wrong value => error category
     */
    public function misconceptions(): array {
        $a = $this->a;
        $b = $this->b;
        $diagnostics = [];

        // Double negative ("minus minus") treated as minus: a - (-b) computed as a - |b|.
        if ($this->subtract && $b->numerator < 0) {
            $this->add($diagnostics, $a->add($b), 'double_negative');
        }

        // Effective addition a + c with c = ±b: magnitudes added although signs differ.
        $c = $this->subtract ? $b->negate() : $b;
        if (($a->numerator < 0) !== ($c->numerator < 0)) {
            $magnitude = $a->abs()->add($c->abs());
            $larger = $a->abs()->compare($c->abs()) >= 0 ? $a : $c;
            $this->add($diagnostics, $larger->numerator < 0 ? $magnitude->negate() : $magnitude, 'magnitude_error');
        }

        // Fractions: numerators and denominators added separately.
        if (!$a->is_integer() || !$b->is_integer()) {
            $this->add(
                $diagnostics,
                new fraction($a->numerator + $c->numerator, $a->denominator + $c->denominator),
                'added_denominators'
            );
        }

        if (!$this->result->is_zero()) {
            $this->add($diagnostics, $this->result->negate(), 'sign_error');
        }

        return $diagnostics;
    }

    /**
     * Expression for display, e.g. "−7 − (−3)".
     */
    public function expression(): string {
        return self::operand($this->a, false)
            . ($this->subtract ? " \u{2212} " : ' + ')
            . self::operand($this->b, true);
    }

    /**
     * Adds a wrong result with its category unless it equals the correct result or is already listed.
     *
     * @param array<string, string> $diagnostics
     * @param fraction $wrong Wrong result
     * @param string $category Error category
     */
    private function add(array &$diagnostics, fraction $wrong, string $category): void {
        if ($wrong->equals($this->result)) {
            return;
        }
        $key = (string)$wrong;
        if (!isset($diagnostics[$key])) {
            $diagnostics[$key] = $category;
        }
    }

    /**
     * Display form of an operand, negative numbers in brackets if requested.
     *
     * @param fraction $value Operand
     * @param bool $bracketnegative Put negative values in brackets
     * @return string
     */
    private static function operand(fraction $value, bool $bracketnegative): string {
        $text = $value->display();
        return ($bracketnegative && $value->numerator < 0) ? '(' . $text . ')' : $text;
    }
}
