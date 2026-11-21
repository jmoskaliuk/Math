<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use mod_mathetrainer\validator\answer_validator;

/**
 * Tests for answer checking and error categories.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\validator\answer_validator
 * @license    MIT
 */
final class answer_validator_test extends \basic_testcase {
    private const NUMERIC = ['type' => 'numeric', 'promptkey' => 'prompt_calculate', 'expression' => '−7 + 3'];
    private const EXPECTED = ['answer' => '-4', 'diagnostics' => ['4' => 'sign_error', '-10' => 'magnitude_error']];

    public function test_numeric_correct_in_any_notation(): void {
        $validator = new answer_validator();
        foreach (['-4', "\u{2212}4", '-8/2', '-4,0', ' -4 '] as $input) {
            $this->assertTrue($validator->check(self::NUMERIC, self::EXPECTED, $input)['correct'], $input);
        }
    }

    public function test_numeric_error_categories(): void {
        $validator = new answer_validator();
        $this->assertSame('sign_error', $validator->check(self::NUMERIC, self::EXPECTED, '4')['category']);
        $this->assertSame('magnitude_error', $validator->check(self::NUMERIC, self::EXPECTED, '-10')['category']);
        $this->assertSame('wrong', $validator->check(self::NUMERIC, self::EXPECTED, '7')['category']);
        $this->assertSame('format', $validator->check(self::NUMERIC, self::EXPECTED, 'vier')['category']);
        $this->assertSame('empty', $validator->check(self::NUMERIC, self::EXPECTED, '   ')['category']);
    }

    public function test_unreduced_fraction_is_correct_with_hint(): void {
        $validator = new answer_validator();
        $result = $validator->check(self::NUMERIC, ['answer' => '1/2', 'diagnostics' => []], '2/4');
        $this->assertTrue($result['correct']);
        $this->assertSame('notreduced', $result['hint']);
    }

    public function test_choice(): void {
        $validator = new answer_validator();
        $payload = ['type' => 'choice', 'options' => ['<', '=', '>']];
        $expected = ['answer' => '<', 'diagnostics' => ['>' => 'denominator_misconception', '=' => 'not_equal']];
        $this->assertTrue($validator->check($payload, $expected, '<')['correct']);
        $this->assertSame('denominator_misconception', $validator->check($payload, $expected, '>')['category']);
        $this->assertSame('not_equal', $validator->check($payload, $expected, '=')['category']);
        $this->assertSame('format', $validator->check($payload, $expected, 'kleiner')['category']);
    }
}
