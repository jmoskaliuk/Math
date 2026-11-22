<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Convert between fractions and decimals (grade 6, AFB I/II).
 *
 * Only fractions with terminating decimals are used. The payload "format"
 * tells the validator which notation the answer must use, so "3/4" is not
 * accepted when a decimal is asked for.
 *
 * Diagnostics:
 *  - digits_copied:     3/4 written as 0,34 or 3,4
 *  - place_value_error: 0,35 written as 35/10 or 35/1000
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\fraction;
use mod_mathetrainer\local\random;

final class decimal_convert implements exercise_generator {
    private const DENOMINATORS = [2, 4, 5, 8, 10, 20, 25, 50, 100];

    public function generate(int $seed): array {
        $rng = new random($seed);

        $denominator = $rng->pick(self::DENOMINATORS);
        // Proper fraction, shown and expected in lowest terms.
        $value = new fraction($rng->int(1, $denominator - 1), $denominator);

        $decimal = self::to_decimal($value);
        $diagnostics = [];

        if ($rng->chance(1, 2)) {
            // Fraction -> decimal.
            $n = $value->numerator;
            $d = $value->denominator;
            foreach (["0.$n$d", "$n.$d"] as $wrong) {
                $parsed = fraction::parse($wrong);
                if ($parsed !== null && !$parsed->equals($value)) {
                    $diagnostics[(string)$parsed] ??= 'digits_copied';
                }
            }
            return [
                'payload' => [
                    'type' => 'numeric',
                    'format' => 'decimal',
                    'promptkey' => 'prompt_to_decimal',
                    'expression' => $n . '/' . $d,
                ],
                'expected' => [
                    'answer' => (string)$value,
                    'display' => str_replace('.', ',', $decimal),
                    'diagnostics' => $diagnostics,
                ],
            ];
        }

        // Decimal -> fraction.
        $digits = strlen(explode('.', $decimal)[1]);
        $significant = (int)str_replace('.', '', ltrim($decimal, '0'));
        foreach ([$digits - 1, $digits + 1] as $wrongdigits) {
            if ($wrongdigits < 0) {
                continue;
            }
            $wrong = new fraction($significant, 10 ** $wrongdigits);
            if (!$wrong->equals($value)) {
                $diagnostics[(string)$wrong] ??= 'place_value_error';
            }
        }
        return [
            'payload' => [
                'type' => 'numeric',
                'format' => 'fraction',
                'promptkey' => 'prompt_to_fraction',
                'expression' => str_replace('.', ',', $decimal),
            ],
            'expected' => [
                'answer' => (string)$value,
                'diagnostics' => $diagnostics,
            ],
        ];
    }

    /**
     * Exact decimal notation (with point) of a fraction whose denominator divides a power of ten.
     */
    private static function to_decimal(fraction $value): string {
        for ($digits = 1; $digits <= 6; $digits++) {
            $scale = 10 ** $digits;
            if ($scale % $value->denominator === 0) {
                $scaled = intdiv($value->numerator * $scale, $value->denominator);
                $integer = intdiv($scaled, $scale);
                $rest = str_pad((string)($scaled % $scale), $digits, '0', STR_PAD_LEFT);
                return $integer . '.' . rtrim($rest, '0');
            }
        }
        throw new \coding_exception('Fraction has no terminating decimal: ' . $value);
    }
}
