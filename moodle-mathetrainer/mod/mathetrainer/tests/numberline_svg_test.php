<?php
// This file is part of the Moodle plugin mod_mathetrainer.

namespace mod_mathetrainer;

use mod_mathetrainer\local\numberline_svg;

/**
 * Tests for the number line SVG renderer.
 *
 * @package    mod_mathetrainer
 * @covers     \mod_mathetrainer\local\numberline_svg
 * @license    MIT
 */
final class numberline_svg_test extends \basic_testcase {
    public function test_render(): void {
        $svg = numberline_svg::render(['divisions' => 4, 'labels' => [0 => '0', '4' => '1'], 'marked' => 3], 'Zahlenstrahl "A"');
        $this->assertStringStartsWith('<svg', $svg);
        $this->assertSame(1, substr_count($svg, '<circle'));
        $this->assertSame(2, substr_count($svg, '<text'));
        // Axis + 5 ticks.
        $this->assertSame(6, substr_count($svg, '<line'));
        $this->assertStringContainsString('aria-label="Zahlenstrahl &quot;A&quot;"', $svg);
        // Marked point at 3/4 of the axis: 30 + 460 * 0.75.
        $this->assertStringContainsString('cx="375"', $svg);
    }

    public function test_labels_are_escaped(): void {
        $svg = numberline_svg::render(['divisions' => 2, 'labels' => [0 => '<b>'], 'marked' => 1], 'x');
        $this->assertStringNotContainsString('<b>', $svg);
        $this->assertStringContainsString('&lt;b&gt;', $svg);
    }
}
