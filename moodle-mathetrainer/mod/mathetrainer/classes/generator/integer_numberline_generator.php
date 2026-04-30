<?php
declare(strict_types=1);

namespace mod_mathetrainer\generator;

final class integer_numberline_generator {
    /**
     * @return array<string, mixed>
     */
    public function generate(int $seed, int $difficulty = 1): array {
        mt_srand($seed);
        $range = $difficulty <= 1 ? 20 : ($difficulty === 2 ? 50 : 100);
        $start = mt_rand(-$range, $range);
        $delta = mt_rand(-10, 10);
        $target = $start + $delta;

        return [
            'templateid' => 'integer_numberline',
            'seed' => $seed,
            'difficulty' => $difficulty,
            'question' => sprintf('Starte bei %d und gehe %d Schritte. Welche Zahl erreichst du?', $start, $delta),
            'expected' => [
                'value' => $target,
            ],
            'meta' => [
                'topic' => 'Rationale Zahlen',
                'grade_from' => 6,
                'grade_to' => 7,
                'error_tags' => ['sign_error', 'counting_error'],
            ],
        ];
    }
}
