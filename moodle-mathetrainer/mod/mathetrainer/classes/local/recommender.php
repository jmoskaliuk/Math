<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Rule-based practice recommendations from per-template results.
 *
 * Pure logic (no database) so that the rules are easy to test and explain:
 *  - not started:          no answers yet
 *  - repeat <template>:    success rate below 60 % with at least 3 answers
 *  - watch <error>:        one error category makes up at least 2 answers
 *                          and at least a third of all wrong answers
 *  - mastered:             at least 80 % overall with at least 5 answers
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

/**
 * Rule-based practice recommendations.
 */
final class recommender {
    /** @var float Templates with a success rate below this are recommended for repetition */
    public const REPEAT_BELOW = 0.6;
    /** @var float Overall success rate from which the competency counts as mastered */
    public const MASTERED_FROM = 0.8;
    /** @var int Minimum answers to a template before it can be recommended for repetition */
    public const MIN_ANSWERS = 3;
    /** @var int Minimum answers overall before the competency can count as mastered */
    public const MIN_ANSWERS_MASTERED = 5;

    /**
     * Recommendation for one learner: status, templates to repeat and an error focus.
     *
     * @param array<string, array{total: int, correct: int}> $templates per template id
     * @param array<string, int> $errors error category => count
     * @return array{status: string, repeat: string[], focus: ?string, rate: ?float}
     *         status: 'notstarted' | 'mastered' | 'practise' | 'ontrack'
     */
    public static function recommend(array $templates, array $errors): array {
        $total = array_sum(array_column($templates, 'total'));
        $correct = array_sum(array_column($templates, 'correct'));
        if ($total === 0) {
            return ['status' => 'notstarted', 'repeat' => [], 'focus' => null, 'rate' => null];
        }
        $rate = $correct / $total;

        $repeat = [];
        foreach ($templates as $templateid => $stats) {
            if ($stats['total'] >= self::MIN_ANSWERS && $stats['correct'] / $stats['total'] < self::REPEAT_BELOW) {
                $repeat[$templateid] = $stats['correct'] / $stats['total'];
            }
        }
        asort($repeat);

        // Formal problems (empty, unreadable input) are not a didactic focus.
        $errors = array_diff_key($errors, array_flip(['empty', 'format', 'wrong']));
        arsort($errors);
        $wrong = $total - $correct;
        $focus = null;
        foreach ($errors as $category => $count) {
            if ($count >= 2 && $wrong > 0 && $count / $wrong >= 1 / 3) {
                $focus = (string)$category;
            }
            break;
        }

        if (!$repeat && $rate >= self::MASTERED_FROM && $total >= self::MIN_ANSWERS_MASTERED) {
            $status = 'mastered';
        } else if ($repeat || $focus !== null) {
            $status = 'practise';
        } else {
            $status = 'ontrack';
        }

        return ['status' => $status, 'repeat' => array_keys($repeat), 'focus' => $focus, 'rate' => $rate];
    }
}
