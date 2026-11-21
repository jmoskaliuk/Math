<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Read off an integer marked on a number line (grade 5-7, AFB I).
 *
 * Only zero and one other tick are labelled, so the learner has to work out
 * the scale. Diagnostics: sign error and "counted ticks instead of using the scale".
 *
 * Payload "numberline": divisions (number of intervals), labels (tick index => text),
 * marked (tick index of the point).
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

use mod_mathetrainer\local\random;

final class integer_numberline implements exercise_generator {
    private const DIVISIONS = 10;

    public function generate(int $seed): array {
        $rng = new random($seed);
        $step = $rng->pick([1, 1, 2, 5, 10]);
        // Index of zero on the line, so the range always contains negative and positive numbers.
        $zero = $rng->int(2, 8);

        do {
            $marked = $rng->int(0, self::DIVISIONS);
        } while ($marked === $zero);
        // Mostly ask for negative numbers, that is the point of the exercise.
        if ($marked > $zero && $rng->chance(2, 3)) {
            $marked = max(0, $zero - ($marked - $zero));
        }

        // Second labelled tick: on the other side of zero than the marked point if possible.
        $candidates = [];
        for ($i = 0; $i <= self::DIVISIONS; $i++) {
            if ($i !== $zero && $i !== $marked && abs($i - $zero) <= 3) {
                $candidates[] = $i;
            }
        }
        $other = $rng->pick($candidates);

        $value = static fn(int $index): int => ($index - $zero) * $step;
        $answer = $value($marked);

        $diagnostics = [];
        if ($step !== 1) {
            $diagnostics[(string)($marked - $zero)] = 'scale_error';
        }
        if (!isset($diagnostics[(string)-$answer])) {
            $diagnostics[(string)-$answer] = 'sign_error';
        }

        return [
            'payload' => [
                'type' => 'numeric',
                'promptkey' => 'prompt_numberline',
                'expression' => '',
                'numberline' => [
                    'divisions' => self::DIVISIONS,
                    'labels' => [
                        $zero => '0',
                        $other => str_replace('-', "\u{2212}", (string)$value($other)),
                    ],
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
