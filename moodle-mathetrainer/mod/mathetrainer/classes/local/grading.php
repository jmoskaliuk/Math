<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Turns attempt scores (0..1) into a gradebook value.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

/**
 * Grading methods and conversion of scores to grades.
 */
final class grading {
    /** @var int Grade is the highest attempt score */
    public const METHOD_HIGHEST = 1;
    /** @var int Grade is the average of the attempt scores */
    public const METHOD_AVERAGE = 2;
    /** @var int Grade is the score of the first attempt */
    public const METHOD_FIRST = 3;
    /** @var int Grade is the score of the last attempt */
    public const METHOD_LAST = 4;

    /**
     * Available grading methods.
     *
     * @return array<int, string> method => language string key
     */
    public static function methods(): array {
        return [
            self::METHOD_HIGHEST => 'grademethod_highest',
            self::METHOD_AVERAGE => 'grademethod_average',
            self::METHOD_FIRST => 'grademethod_first',
            self::METHOD_LAST => 'grademethod_last',
        ];
    }

    /**
     * Aggregated score of the finished attempts of one learner.
     *
     * @param float[] $scores scores 0..1 in attempt order (oldest first)
     * @return float|null null if there is no finished attempt
     */
    public static function aggregate(array $scores, int $method): ?float {
        $scores = array_values(array_map('floatval', $scores));
        if (!$scores) {
            return null;
        }
        return match ($method) {
            self::METHOD_AVERAGE => array_sum($scores) / count($scores),
            self::METHOD_FIRST => $scores[0],
            self::METHOD_LAST => $scores[count($scores) - 1],
            default => max($scores),
        };
    }

    /**
     * Raw grade for the gradebook.
     *
     * @param float $score aggregated score 0..1
     * @param int $grade activity grade: > 0 maximum points, < 0 scale (with $scaleitems items)
     * @param int $scaleitems number of items of the scale (only for scales)
     */
    public static function rawgrade(float $score, int $grade, int $scaleitems = 0): ?float {
        $score = max(0.0, min(1.0, $score));
        if ($grade > 0) {
            return round($score * $grade, 5);
        }
        if ($grade < 0 && $scaleitems > 0) {
            // Scale items are numbered 1..n; 0 % maps to the first, 100 % to the last item.
            return (float)(1 + (int)round($score * ($scaleitems - 1)));
        }
        return null;
    }
}
