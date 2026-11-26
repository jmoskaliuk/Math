<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use mod_mathetrainer\bloom\package_builder;
use mod_mathetrainer\generator\registry;
use mod_mathetrainer\local\fraction;

/**
 * Tests for the Bloom package builder.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\bloom\package_builder
 * @license    MIT
 */
final class bloom_package_builder_test extends \basic_testcase {
    /**
     * Builder with a translator that returns "key" or "key:argument".
     *
     * @return package_builder
     */
    private function builder(): package_builder {
        return new package_builder(static fn(string $key, $a = null): string => $a === null ? $key : $key . ':' . $a);
    }

    public function test_structure_and_determinism(): void {
        $package = $this->builder()->build(['rational_addition', 'fraction_compare'], 6, 42);
        $this->assertSame(package_builder::FORMAT, $package['format']);
        // Intro + 6 exercises + result.
        $this->assertCount(8, $package['slides']);
        $this->assertSame('info', $package['slides'][0]['type']);
        $this->assertSame('result', $package['slides'][7]['type']);
        $this->assertSame($package, $this->builder()->build(['rational_addition', 'fraction_compare'], 6, 42));
        $other = $this->builder()->build(['rational_addition', 'fraction_compare'], 6, 43);
        $this->assertNotSame($package['slides'], $other['slides']);
        $this->assertCount(2, $package['concept']['goals']);
    }

    public function test_every_slide_is_solvable(): void {
        foreach ([package_builder::STYLE_INPUT, package_builder::STYLE_MC, package_builder::STYLE_MIXED] as $style) {
            $package = $this->builder()->build(registry::templateids(), 30, 7, $style);
            foreach (array_slice($package['slides'], 1, -1) as $slide) {
                $this->assertArrayHasKey('mathetrainer', $slide);
                if ($slide['type'] === 'mc') {
                    $correct = array_filter($slide['options'], static fn($o) => $o['correct']);
                    $this->assertCount(1, $correct, 'Exactly one correct option.');
                    $this->assertGreaterThanOrEqual(2, count($slide['options']));
                    $texts = array_column($slide['options'], 'text');
                    $this->assertSame(count($texts), count(array_unique($texts)), 'No duplicate options.');
                    foreach ($slide['options'] as $option) {
                        $this->assertNotSame('', $option['feedback']);
                    }
                } else {
                    $this->assertSame('input', $slide['type']);
                    $this->assertMatchesRegularExpression('/^aufgabe_\d+$/', $slide['name'], 'Bloom needs an input name.');
                    $this->assertNotEmpty($slide['answers']);
                    $this->assertTrue($slide['scored']);
                }
            }
        }
    }

    /**
     * Every exercise slide covers a goal and a key point of the concept (Bloom's coverage check).
     */
    public function test_goal_and_keypoint_coverage(): void {
        $package = $this->builder()->build(['integer_numberline', 'rational_addition', 'sign_error_analysis'], 9, 1);
        $goals = array_column($package['concept']['goals'], 'id');
        $keypoints = array_column($package['concept']['keyPoints'], 'id');
        $covered = [];
        foreach (array_slice($package['slides'], 1, -1) as $slide) {
            $this->assertContains($slide['goals'][0], $goals);
            $this->assertContains($slide['keyPoints'][0], $keypoints);
            $covered[$slide['goals'][0]] = true;
        }
        $this->assertCount(count($goals), $covered, 'Every goal is covered by at least one slide.');
        $this->assertSame($keypoints, $package['slides'][0]['keyPoints']);
    }

    public function test_mc_distractors_are_misconceptions(): void {
        // Use style mc so that numeric exercises become multiple choice.
        $package = $this->builder()->build(['rational_addition'], 10, 3, package_builder::STYLE_MC);
        $explained = 0;
        foreach (array_slice($package['slides'], 1, -1) as $slide) {
            foreach ($slide['options'] as $option) {
                if (!$option['correct'] && $option['feedback'] !== 'error_wrong') {
                    $this->assertStringStartsWith('error_', $option['feedback']);
                    $explained++;
                }
            }
        }
        $this->assertGreaterThan(10, $explained, 'Most distractors carry a specific explanation.');
    }

    public function test_answer_variants(): void {
        $variants = package_builder::answer_variants([
            'payload' => ['type' => 'numeric'],
            'expected' => ['answer' => '-3/4', 'diagnostics' => []],
        ]);
        foreach (['-3/4', "\u{2212}3/4", '-0.75', '-0,75', "\u{2212}0,75"] as $expected) {
            $this->assertContains($expected, $variants);
        }
        // A decimal is asked for: the fraction is not accepted.
        $variants = package_builder::answer_variants([
            'payload' => ['type' => 'numeric', 'format' => 'decimal'],
            'expected' => ['answer' => '3/4', 'display' => '0,75', 'diagnostics' => []],
        ]);
        $this->assertNotContains('3/4', $variants);
        $this->assertContains('0,75', $variants);
        foreach ($variants as $variant) {
            $this->assertTrue(fraction::parse($variant)->equals(new fraction(3, 4)));
        }
    }

    public function test_numberline_assets(): void {
        $package = $this->builder()->build(['integer_numberline'], 2, 5);
        $this->assertCount(2, $package['assets']);
        $slide = $package['slides'][1];
        $this->assertStringStartsWith('asset:', $slide['image']['media']);
        $asset = $package['assets'][substr($slide['image']['media'], 6)];
        $this->assertStringStartsWith('<svg', $asset['content']);
    }
}
