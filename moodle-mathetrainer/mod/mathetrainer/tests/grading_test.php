<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use mod_mathetrainer\local\grading;
use mod_mathetrainer\local\recommender;

/**
 * Tests for grade aggregation and practice recommendations.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\local\grading
 * @covers     \mod_mathetrainer\local\recommender
 * @license    MIT
 */
final class grading_test extends \basic_testcase {
    public function test_aggregate(): void {
        $scores = [0.4, 1.0, 0.6];
        $this->assertSame(1.0, grading::aggregate($scores, grading::METHOD_HIGHEST));
        $this->assertSame(0.4, grading::aggregate($scores, grading::METHOD_FIRST));
        $this->assertSame(0.6, grading::aggregate($scores, grading::METHOD_LAST));
        $this->assertSame(round(2.0 / 3, 10), round(grading::aggregate($scores, grading::METHOD_AVERAGE), 10));
        $this->assertSame(null, grading::aggregate([], grading::METHOD_HIGHEST));
    }

    public function test_rawgrade(): void {
        $this->assertSame(80.0, grading::rawgrade(0.8, 100));
        $this->assertSame(12.0, grading::rawgrade(0.8, 15));
        $this->assertSame(null, grading::rawgrade(0.8, 0));
        // Scale with 4 items: 0 % -> 1, 100 % -> 4, 50 % -> 3 (rounded).
        $this->assertSame(1.0, grading::rawgrade(0.0, -2, 4));
        $this->assertSame(4.0, grading::rawgrade(1.0, -2, 4));
        $this->assertSame(3.0, grading::rawgrade(0.5, -2, 4));
        $this->assertSame(100.0, grading::rawgrade(1.7, 100), 'Score is clamped.');
    }

    public function test_recommend_not_started(): void {
        $this->assertSame('notstarted', recommender::recommend([], [])['status']);
    }

    public function test_recommend_repeat_and_focus(): void {
        $result = recommender::recommend(
            [
                'rational_addition' => ['total' => 5, 'correct' => 1],
                'integer_numberline' => ['total' => 5, 'correct' => 5],
                'sign_error_analysis' => ['total' => 2, 'correct' => 0],
            ],
            ['sign_error' => 3, 'wrong' => 2, 'magnitude_error' => 1]
        );
        $this->assertSame('practise', $result['status']);
        // Only templates with enough answers are recommended for repetition.
        $this->assertSame(['rational_addition'], $result['repeat']);
        $this->assertSame('sign_error', $result['focus']);
    }

    public function test_recommend_mastered(): void {
        $result = recommender::recommend(['fraction_compare' => ['total' => 10, 'correct' => 9]], ['relation_reversed' => 1]);
        $this->assertSame('mastered', $result['status']);
        $this->assertSame([], $result['repeat']);
        $this->assertSame(null, $result['focus']);
    }

    public function test_recommend_formal_errors_are_no_focus(): void {
        $result = recommender::recommend(['linear_equation' => ['total' => 4, 'correct' => 3]], ['empty' => 1]);
        $this->assertSame('ontrack', $result['status']);
        $this->assertSame(null, $result['focus']);
    }
}
