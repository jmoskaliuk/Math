<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use mod_mathetrainer\local\fraction;

/**
 * Tests for the exact fraction type and learner input parsing.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\local\fraction
 * @license    MIT
 */
final class fraction_test extends \basic_testcase {
    public function test_normalises_to_lowest_terms_and_sign(): void {
        $this->assertSame('-3/4', (string)new fraction(6, -8));
        $this->assertSame('5', (string)new fraction(10, 2));
        $this->assertSame('0', (string)new fraction(0, 7));
    }

    public function test_arithmetic_and_compare(): void {
        $this->assertSame('5/6', (string)(new fraction(1, 2))->add(new fraction(1, 3)));
        $this->assertSame('-1/6', (string)(new fraction(1, 3))->subtract(new fraction(1, 2)));
        $this->assertSame(-1, (new fraction(1, 3))->compare(new fraction(1, 2)));
        $this->assertTrue((new fraction(2, 4))->equals(new fraction(1, 2)));
    }

    /**
     * @dataProvider parse_provider
     */
    public function test_parse(string $input, ?string $expected): void {
        $parsed = fraction::parse($input);
        $this->assertSame($expected, $parsed === null ? null : (string)$parsed);
    }

    public static function parse_provider(): array {
        return [
            'integer' => ['-7', '-7'],
            'plus sign' => ['+4', '4'],
            'fraction' => ['3/4', '3/4'],
            'negative fraction' => ['-6/8', '-3/4'],
            'negative denominator' => ['3/-4', '-3/4'],
            'typographic minus' => ["\u{2212}5", '-5'],
            'spaces' => [' - 1 / 2 ', '-1/2'],
            'decimal comma' => ['0,75', '3/4'],
            'decimal point' => ['-1.5', '-3/2'],
            'leading comma' => [',5', '1/2'],
            'empty' => ['', null],
            'text' => ['abc', null],
            'zero denominator' => ['1/0', null],
            'double sign' => ['--3', null],
        ];
    }

    public function test_unreduced_input(): void {
        $this->assertTrue(fraction::is_unreduced_input('2/4'));
        $this->assertFalse(fraction::is_unreduced_input('1/2'));
        $this->assertFalse(fraction::is_unreduced_input('0,5'));
    }
}
