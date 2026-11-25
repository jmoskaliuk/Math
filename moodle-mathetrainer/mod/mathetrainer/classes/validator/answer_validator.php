<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Checks a learner response against the expected answer of a generated item
 * and determines an error category. Correctness never depends on AI.
 *
 * Categories: null (correct), 'empty', 'format', 'format_decimal',
 * 'format_fraction', a generator diagnostic
 * (e.g. 'sign_error') or 'wrong'.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\validator;

use mod_mathetrainer\local\fraction;

/**
 * Checks learner responses and assigns an error category.
 */
final class answer_validator {
    /**
     * Checks a response against the expected answer of an item.
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $expected
     * @param string $response Learner response
     * @return array{correct: bool, category: ?string, hint: ?string, normalised: ?string}
     */
    public function check(array $payload, array $expected, string $response): array {
        $response = trim($response);
        if ($response === '') {
            return self::result(false, 'empty');
        }

        $diagnostics = (array)($expected['diagnostics'] ?? []);
        $answer = (string)$expected['answer'];

        if (($payload['type'] ?? '') === 'choice') {
            if (!in_array($response, (array)($payload['options'] ?? []), true)) {
                return self::result(false, 'format');
            }
            if ($response === $answer) {
                return self::result(true, null, null, $response);
            }
            return self::result(false, $diagnostics[$response] ?? 'wrong', null, $response);
        }

        $given = fraction::parse($response);
        if ($given === null) {
            return self::result(false, 'format');
        }
        // Conversion tasks require a specific notation (e.g. "3/4" is not a decimal).
        $format = $payload['format'] ?? null;
        if ($format === 'decimal' && !preg_match('/[.,]/', $response)) {
            return self::result(false, 'format_decimal', null, (string)$given);
        }
        if ($format === 'fraction' && !str_contains($response, '/')) {
            return self::result(false, 'format_fraction', null, (string)$given);
        }
        $correct = fraction::parse($answer);
        if ($correct === null) {
            throw new \coding_exception('Invalid expected answer: ' . $answer);
        }
        if ($given->equals($correct)) {
            $hint = fraction::is_unreduced_input($response) ? 'notreduced' : null;
            return self::result(true, null, $hint, (string)$given);
        }
        return self::result(false, $diagnostics[(string)$given] ?? 'wrong', null, (string)$given);
    }

    /**
     * Builds a check result.
     *
     * @param bool $correct Whether the response is correct
     * @param string|null $category Error category, null if correct
     * @param string|null $hint Additional hint (e.g. "notreduced")
     * @param string|null $normalised Canonical form of the response
     * @return array{correct: bool, category: ?string, hint: ?string, normalised: ?string}
     */
    private static function result(bool $correct, ?string $category, ?string $hint = null, ?string $normalised = null): array {
        return ['correct' => $correct, 'category' => $category, 'hint' => $hint, 'normalised' => $normalised];
    }
}
