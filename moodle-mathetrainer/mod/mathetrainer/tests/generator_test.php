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
                $expressions[] = $generator->generate($templateid, $seed)['payload']['expression'];
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
        $this->assertSame(['rational_addition'], registry::find('Rationale Zahlen', 7));
        $this->assertSame(['fraction_compare'], registry::find('Brüche', 6));
        // No template for grade 2 in this topic: fall back to the topic.
        $this->assertSame(['fraction_compare'], registry::find('Brüche', 2));
        // Unknown topic: fall back to everything.
        $this->assertSame(registry::templateids(), registry::find('Geometrie', 5));
    }

    public function test_generated_answers_validate_as_correct(): void {
        $generator = new task_generator();
        $validator = new answer_validator();
        foreach (registry::templateids() as $templateid) {
            for ($seed = 1; $seed <= 100; $seed++) {
                $exercise = $generator->generate($templateid, $seed);
                $result = $validator->check($exercise['payload'], $exercise['expected'], $exercise['expected']['answer']);
                $this->assertTrue($result['correct'], "$templateid seed $seed");
                foreach ($exercise['expected']['diagnostics'] as $wrong => $category) {
                    $result = $validator->check($exercise['payload'], $exercise['expected'], (string)$wrong);
                    $this->assertFalse($result['correct']);
                    $this->assertSame($category, $result['category']);
                }
            }
        }
    }
}
