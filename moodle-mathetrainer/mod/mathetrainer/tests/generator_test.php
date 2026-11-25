<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use mod_mathetrainer\generator\registry;
use mod_mathetrainer\generator\task_generator;
use mod_mathetrainer\local\fraction;
use mod_mathetrainer\validator\answer_validator;

/**
 * Tests for the deterministic exercise generators.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\generator\rational_addition
 * @covers     \mod_mathetrainer\generator\fraction_compare
 * @covers     \mod_mathetrainer\generator\registry
 * @covers     \mod_mathetrainer\generator\integer_numberline
 * @covers     \mod_mathetrainer\generator\fraction_numberline
 * @covers     \mod_mathetrainer\generator\linear_equation
 * @covers     \mod_mathetrainer\generator\sign_error_analysis
 * @covers     \mod_mathetrainer\generator\order_of_operations
 * @covers     \mod_mathetrainer\generator\decimal_convert
 * @license    MIT
 */
final class generator_test extends \basic_testcase {
    public function test_same_seed_gives_same_exercise(): void {
        $generator = new task_generator();
        foreach (registry::templateids() as $templateid) {
            for ($seed = 1; $seed <= 50; $seed++) {
                $this->assertSame(
                    $generator->generate($templateid, $seed),
                    $generator->generate($templateid, $seed),
                    "$templateid seed $seed"
                );
            }
        }
    }

    public function test_different_seeds_vary(): void {
        $generator = new task_generator();
        foreach (registry::templateids() as $templateid) {
            $expressions = [];
            for ($seed = 1; $seed <= 50; $seed++) {
                $expressions[] = json_encode($generator->generate($templateid, $seed)['payload']);
            }
            $this->assertGreaterThan(20, count(array_unique($expressions)), $templateid);
        }
    }

    /**
     * Recomputes the result from the displayed expression to make sure the
     * stored answer is mathematically correct.
     */
    public function test_rational_addition_answer_is_correct(): void {
        $generator = new task_generator();
        for ($seed = 1; $seed <= 500; $seed++) {
            $exercise = $generator->generate('rational_addition', $seed);
            $expression = str_replace("\u{2212}", '-', $exercise['payload']['expression']);
            $this->assertMatchesRegularExpression('/^(\S+) ([+-]) \(?([^()]+)\)?$/', $expression);
            preg_match('/^(\S+) ([+-]) \(?([^()]+)\)?$/', $expression, $m);
            $a = fraction::parse($m[1]);
            $b = fraction::parse($m[3]);
            $expected = $m[2] === '+' ? $a->add($b) : $a->subtract($b);
            $this->assertSame((string)$expected, $exercise['expected']['answer'], "seed $seed: $expression");
            // A diagnostic must never coincide with the correct answer.
            $this->assertArrayNotHasKey($exercise['expected']['answer'], $exercise['expected']['diagnostics']);
            // At least one operand is negative.
            $this->assertTrue($a->numerator < 0 || $b->numerator < 0, "seed $seed: $expression");
        }
    }

    public function test_fraction_compare_answer_is_correct(): void {
        $generator = new task_generator();
        $seen = [];
        for ($seed = 1; $seed <= 500; $seed++) {
            $exercise = $generator->generate('fraction_compare', $seed);
            preg_match('#^(\d+)/(\d+) \S (\d+)/(\d+)$#u', $exercise['payload']['expression'], $m);
            $this->assertCount(5, $m, $exercise['payload']['expression']);
            $cmp = (new fraction((int)$m[1], (int)$m[2]))->compare(new fraction((int)$m[3], (int)$m[4]));
            $expected = ['-1' => '<', '0' => '=', '1' => '>'][(string)$cmp];
            $this->assertSame($expected, $exercise['expected']['answer']);
            $this->assertArrayNotHasKey($expected, $exercise['expected']['diagnostics']);
            $seen[$expected] = true;
        }
        $this->assertCount(3, $seen, 'All three relations occur.');
    }

    public function test_registry_find(): void {
        $this->assertSame(['fraction_compare'], registry::find('Brüche', 7));
        $this->assertSame(['order_of_operations'], registry::find('Natürliche Zahlen', 5));
        $this->assertSame(['decimal_convert'], registry::find('Dezimalzahlen', 6));
        $this->assertSame(['fraction_numberline', 'fraction_compare'], registry::find('Brüche', 6));
        $this->assertSame(
            ['integer_numberline', 'rational_addition', 'sign_error_analysis'],
            registry::find('Rationale Zahlen', 7)
        );
        // Requirement level narrows the selection.
        $this->assertSame(['sign_error_analysis'], registry::find('Rationale Zahlen', 7, 'AFB III'));
        $this->assertSame(['integer_numberline', 'rational_addition'], registry::find('Rationale Zahlen', 7, 'AFB I'));
        // Level without a match: ignored rather than leaving the activity empty.
        $this->assertSame(['fraction_numberline'], registry::find('Brüche', 5, 'AFB III'));
        // No template for grade 2 in this topic: fall back to the topic.
        $this->assertSame(['fraction_numberline', 'fraction_compare'], registry::find('Brüche', 2));
        // Unknown topic: fall back to everything.
        $this->assertSame(registry::templateids(), registry::find('Geometrie', 5));
    }

    public function test_registry_find_with_curriculum_templates(): void {
        // Templates from the curriculum override topic and grade, unknown ids are ignored.
        $this->assertSame(
            ['fraction_numberline', 'fraction_compare'],
            registry::find('Rationale Zahlen', 9, null, ['fraction_compare', 'fraction_numberline', 'unknown'])
        );
        // Only unknown ids: fall back to the normal selection.
        $this->assertSame(['linear_equation'], registry::find('Gleichungen', 7, null, ['unknown']));
    }

    public function test_order_of_operations(): void {
        $generator = new task_generator();
        $seen = [];
        for ($seed = 1; $seed <= 500; $seed++) {
            $exercise = $generator->generate('order_of_operations', $seed);
            // Evaluate with PHP's own operator precedence.
            $php = strtr($exercise['payload']['expression'], ["\u{00B7}" => '*', "\u{2212}" => '-', ':' => '/']);
            $this->assertMatchesRegularExpression('/^[\d\s+\-*\/()]+$/', $php);
            $value = self::evaluate($php);
            $this->assertSame((string)$value, $exercise['expected']['answer'], "seed $seed: $php");
            $this->assertGreaterThanOrEqual(0, $value);
            $this->assertArrayNotHasKey($exercise['expected']['answer'], $exercise['expected']['diagnostics']);
            foreach ($exercise['expected']['diagnostics'] as $category) {
                $seen[$category] = true;
            }
        }
        $this->assertCount(2, $seen, 'Both misconceptions occur.');
    }

    /**
     * Tiny recursive-descent evaluator for + - * / and brackets (independent of the generator).
     */
    private static function evaluate(string $expression): int {
        preg_match_all('/\d+|[-+*\/()]/', $expression, $m);
        $tokens = $m[0];
        $pos = 0;
        $factor = static function () use (&$tokens, &$pos, &$sum): int {
            $token = $tokens[$pos++];
            if ($token === '(') {
                $value = $sum();
                $pos++; // Closing bracket.
                return $value;
            }
            return (int)$token;
        };
        $product = static function () use (&$tokens, &$pos, $factor): int {
            $value = $factor();
            while (isset($tokens[$pos]) && in_array($tokens[$pos], ['*', '/'], true)) {
                $value = $tokens[$pos++] === '*' ? $value * $factor() : intdiv($value, $factor());
            }
            return $value;
        };
        $sum = static function () use (&$tokens, &$pos, $product): int {
            $value = $product();
            while (isset($tokens[$pos]) && in_array($tokens[$pos], ['+', '-'], true)) {
                $value = $tokens[$pos++] === '+' ? $value + $product() : $value - $product();
            }
            return $value;
        };
        return $sum();
    }

    public function test_decimal_convert(): void {
        $generator = new task_generator();
        $validator = new answer_validator();
        $formats = [];
        for ($seed = 1; $seed <= 500; $seed++) {
            $exercise = $generator->generate('decimal_convert', $seed);
            $payload = $exercise['payload'];
            $expected = $exercise['expected'];
            $value = fraction::parse($expected['answer']);
            $formats[$payload['format']] = true;

            if ($payload['format'] === 'decimal') {
                // Shown fraction and displayed decimal have the same value.
                $this->assertTrue(fraction::parse($payload['expression'])->equals($value));
                $this->assertTrue(fraction::parse($expected['display'])->equals($value));
                $this->assertStringContainsString(',', $expected['display']);
                $this->assertSame('format_decimal', $validator->check($payload, $expected, $expected['answer'])['category']);
            } else {
                $this->assertTrue(fraction::parse($payload['expression'])->equals($value));
                $this->assertSame('format_fraction', $validator->check($payload, $expected, $payload['expression'])['category']);
            }
        }
        $this->assertCount(2, $formats);

        // Typical mistakes, checked on fixed examples.
        $payload = ['type' => 'numeric', 'format' => 'decimal'];
        $expected = ['answer' => '3/4', 'diagnostics' => ['17/50' => 'digits_copied', '17/5' => 'digits_copied']];
        $this->assertSame('digits_copied', $validator->check($payload, $expected, '0,34')['category']);
        $this->assertTrue($validator->check($payload, $expected, '0,75')['correct']);
        $payload = ['type' => 'numeric', 'format' => 'fraction'];
        $expected = ['answer' => '7/20', 'diagnostics' => ['7/2' => 'place_value_error']];
        $this->assertSame('place_value_error', $validator->check($payload, $expected, '35/10')['category']);
        $this->assertTrue($validator->check($payload, $expected, '35/100')['correct']);
    }

    public function test_integer_numberline(): void {
        $generator = new task_generator();
        $negatives = 0;
        for ($seed = 1; $seed <= 300; $seed++) {
            $exercise = $generator->generate('integer_numberline', $seed);
            $line = $exercise['payload']['numberline'];
            $labels = $line['labels'];
            $this->assertCount(2, $labels);
            $zero = array_search('0', $labels, true);
            $this->assertIsInt($zero);
            $other = array_key_first(array_diff_key($labels, [$zero => true]));
            $step = intdiv((int)str_replace("\u{2212}", '-', $labels[$other]), $other - $zero);
            $this->assertGreaterThan(0, $step);
            $this->assertNotSame($zero, $line['marked']);
            $this->assertSame((string)(($line['marked'] - $zero) * $step), $exercise['expected']['answer']);
            $negatives += (int)$exercise['expected']['answer'] < 0 ? 1 : 0;
        }
        $this->assertGreaterThan(150, $negatives, 'Mostly negative numbers are asked.');
    }

    public function test_fraction_numberline(): void {
        $generator = new task_generator();
        for ($seed = 1; $seed <= 300; $seed++) {
            $exercise = $generator->generate('fraction_numberline', $seed);
            $line = $exercise['payload']['numberline'];
            $parts = array_search('1', $line['labels'], true);
            $this->assertIsInt($parts);
            $this->assertSame((string)new fraction($line['marked'], $parts), $exercise['expected']['answer']);
            $this->assertFalse(fraction::parse($exercise['expected']['answer'])->is_integer());
        }
    }

    public function test_linear_equation_solution_satisfies_equation(): void {
        $generator = new task_generator();
        for ($seed = 1; $seed <= 500; $seed++) {
            $exercise = $generator->generate('linear_equation', $seed);
            $expression = str_replace("\u{2212}", '-', $exercise['payload']['expression']);
            $this->assertMatchesRegularExpression('/^(-?\d*)x ([+-]) (\d+) = (-?\d+)$/', $expression);
            preg_match('/^(-?\d*)x ([+-]) (\d+) = (-?\d+)$/', $expression, $m);
            $a = match ($m[1]) {
                '' => 1, '-' => -1, default => (int)$m[1]
            };
            $b = $m[2] === '+' ? (int)$m[3] : -(int)$m[3];
            $x = (int)$exercise['expected']['answer'];
            $this->assertSame((int)$m[4], $a * $x + $b, "seed $seed: $expression");
        }
    }

    public function test_sign_error_analysis_shows_wrong_result(): void {
        $generator = new task_generator();
        for ($seed = 1; $seed <= 300; $seed++) {
            $exercise = $generator->generate('sign_error_analysis', $seed);
            $expression = str_replace("\u{2212}", '-', $exercise['payload']['expression']);
            $this->assertMatchesRegularExpression('/^(\S+) ([+-]) \(?([^()]+)\)? = (\S+)$/', $expression);
            preg_match('/^(\S+) ([+-]) \(?([^()]+)\)? = (\S+)$/', $expression, $m);
            $correct = $m[2] === '+' ? (int)$m[1] + (int)$m[3] : (int)$m[1] - (int)$m[3];
            $this->assertSame((string)$correct, $exercise['expected']['answer'], "seed $seed: $expression");
            $this->assertNotSame((string)$correct, $m[4], 'The shown result must be wrong.');
            $this->assertTrue((int)$m[1] < 0 || (int)$m[3] < 0, "seed $seed: at least one negative operand");
            $this->assertSame('copied_wrong_result', $exercise['expected']['diagnostics'][$m[4]]);
        }
    }

    public function test_generated_answers_validate_as_correct(): void {
        $generator = new task_generator();
        $validator = new answer_validator();
        foreach (registry::templateids() as $templateid) {
            for ($seed = 1; $seed <= 100; $seed++) {
                $exercise = $generator->generate($templateid, $seed);
                $answer = $exercise['expected']['display'] ?? $exercise['expected']['answer'];
                $result = $validator->check($exercise['payload'], $exercise['expected'], $answer);
                $this->assertTrue($result['correct'], "$templateid seed $seed");
                if (isset($exercise['payload']['format'])) {
                    // Notation-bound tasks are covered in test_decimal_convert.
                    continue;
                }
                foreach ($exercise['expected']['diagnostics'] as $wrong => $category) {
                    $result = $validator->check($exercise['payload'], $exercise['expected'], (string)$wrong);
                    $this->assertFalse($result['correct']);
                    $this->assertSame($category, $result['category']);
                }
            }
        }
    }
}
