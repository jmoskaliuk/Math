<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Immutable exact rational number, always stored in lowest terms.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

/**
 * Exact rational number in lowest terms with a positive denominator.
 */
final class fraction {
    /** @var int Numerator (carries the sign) */
    public readonly int $numerator;
    /** @var int Denominator, always positive */
    public readonly int $denominator;

    /**
     * Creates a fraction and reduces it to lowest terms.
     *
     * @param int $numerator Numerator
     * @param int $denominator Denominator, must not be zero
     */
    public function __construct(int $numerator, int $denominator = 1) {
        if ($denominator === 0) {
            throw new \coding_exception('Denominator must not be zero.');
        }
        if ($denominator < 0) {
            $numerator = -$numerator;
            $denominator = -$denominator;
        }
        $gcd = self::gcd(abs($numerator), $denominator);
        $this->numerator = intdiv($numerator, $gcd);
        $this->denominator = intdiv($denominator, $gcd);
    }

    /**
     * Greatest common divisor; 1 if both numbers are zero.
     *
     * @param int $a First number
     * @param int $b Second number
     * @return int
     */
    public static function gcd(int $a, int $b): int {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        return $a === 0 ? 1 : $a;
    }

    /**
     * Parses learner input. Accepts integers ("-7"), fractions ("3/4", "-3/4", "3/-4")
     * and decimals with comma or point ("0,75", "-1.5"). The typographic minus
     * sign (U+2212) and surrounding spaces are tolerated.
     *
     * @return self|null null if the input is not a number.
     */
    public static function parse(string $input): ?self {
        $s = str_replace(["\u{2212}", "\u{2013}", ' ', "\u{00A0}"], ['-', '-', '', ''], trim($input));
        if ($s === '') {
            return null;
        }
        if (preg_match('/^([+-]?\d{1,9})$/', $s, $m)) {
            return new self((int)$m[1]);
        }
        if (preg_match('/^([+-]?\d{1,9})\/([+-]?\d{1,9})$/', $s, $m)) {
            if ((int)$m[2] === 0) {
                return null;
            }
            return new self((int)$m[1], (int)$m[2]);
        }
        if (preg_match('/^([+-]?)(\d{0,9})[.,](\d{1,6})$/', $s, $m)) {
            $denominator = 10 ** strlen($m[3]);
            $numerator = (int)($m[2] === '' ? '0' : $m[2]) * $denominator + (int)$m[3];
            return new self($m[1] === '-' ? -$numerator : $numerator, $denominator);
        }
        return null;
    }

    /**
     * True if the input is written as a fraction that is not in lowest terms (e.g. "2/4").
     */
    public static function is_unreduced_input(string $input): bool {
        $s = str_replace(["\u{2212}", ' '], ['-', ''], trim($input));
        if (!preg_match('/^[+-]?(\d+)\/[+-]?(\d+)$/', $s, $m) || (int)$m[2] === 0) {
            return false;
        }
        return self::gcd((int)$m[1], (int)$m[2]) > 1;
    }

    /**
     * Sum of this and another fraction.
     *
     * @param self $other Fraction to add
     * @return self
     */
    public function add(self $other): self {
        return new self(
            $this->numerator * $other->denominator + $other->numerator * $this->denominator,
            $this->denominator * $other->denominator
        );
    }

    /**
     * Fraction with the opposite sign.
     *
     * @return self
     */
    public function negate(): self {
        return new self(-$this->numerator, $this->denominator);
    }

    /**
     * Difference of this and another fraction.
     *
     * @param self $other Fraction to subtract
     * @return self
     */
    public function subtract(self $other): self {
        return $this->add($other->negate());
    }

    /**
     * Absolute value.
     *
     * @return self
     */
    public function abs(): self {
        return new self(abs($this->numerator), $this->denominator);
    }

    /**
     * Compares this fraction with another one.
     *
     * @param self $other Fraction to compare with
     * @return int -1, 0 or 1
     */
    public function compare(self $other): int {
        return ($this->numerator * $other->denominator) <=> ($other->numerator * $this->denominator);
    }

    /**
     * Whether both fractions have the same value.
     *
     * @param self $other Fraction to compare with
     * @return bool
     */
    public function equals(self $other): bool {
        return $this->compare($other) === 0;
    }

    /**
     * Whether the value is zero.
     *
     * @return bool
     */
    public function is_zero(): bool {
        return $this->numerator === 0;
    }

    /**
     * Whether the value is an integer.
     *
     * @return bool
     */
    public function is_integer(): bool {
        return $this->denominator === 1;
    }

    /**
     * Canonical ASCII form: "-3/4", "5", "0".
     */
    public function __toString(): string {
        return $this->is_integer()
            ? (string)$this->numerator
            : $this->numerator . '/' . $this->denominator;
    }

    /**
     * Display form with typographic minus, e.g. "−3/4".
     */
    public function display(): string {
        return str_replace('-', "\u{2212}", (string)$this);
    }
}
