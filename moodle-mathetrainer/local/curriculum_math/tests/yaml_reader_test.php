<?php
// This file is part of the Moodle plugin local_curriculum_math.

namespace local_curriculum_math;

use local_curriculum_math\importer\yaml_reader;

/**
 * Tests for the built-in YAML reader (used when ext-yaml is missing).
 *
 * Expected values were cross-checked against PyYAML (safe_load).
 *
 * @package    local_curriculum_math
 * @covers     \local_curriculum_math\importer\yaml_reader
 * @license    MIT
 */
final class yaml_reader_test extends \basic_testcase {
    public function test_curriculum_pack(): void {
        $yaml = <<<'YAML'
        framework:
          id: "NRW-MATH-SI"
          version: "draft-0.1"

        # Kompetenzen
        competencies:
          - id: "NRW-MATH-07-RATIONAL-ZAHLEN"
            grade_from: 7
            kmk:
              leitidee: "Zahl und Operation"
              process:
                - "Mit mathematischen Objekten umgehen"
              requirement_levels: ["AFB I", "AFB II"]
            exercise_generators:
              - "integer_numberline"
              - "rational_addition"
          - id: "B"
        YAML;

        $this->assertSame([
            'framework' => ['id' => 'NRW-MATH-SI', 'version' => 'draft-0.1'],
            'competencies' => [
                [
                    'id' => 'NRW-MATH-07-RATIONAL-ZAHLEN',
                    'grade_from' => 7,
                    'kmk' => [
                        'leitidee' => 'Zahl und Operation',
                        'process' => ['Mit mathematischen Objekten umgehen'],
                        'requirement_levels' => ['AFB I', 'AFB II'],
                    ],
                    'exercise_generators' => ['integer_numberline', 'rational_addition'],
                ],
                ['id' => 'B'],
            ],
        ], (new yaml_reader())->parse($yaml));
    }

    public function test_scalars_and_comments(): void {
        $yaml = <<<'YAML'
        a: 1
        b: -2.5
        c: "quoted # not a comment"   # comment
        d: 'it''s'
        e: Schüler's Heft
        f: [1, "x, y", plain]
        g: ~
        h: true
        url: http://example.org/a#frag
        list:
        - same indent
        YAML;

        $this->assertSame([
            'a' => 1,
            'b' => -2.5,
            'c' => 'quoted # not a comment',
            'd' => "it's",
            'e' => "Schüler's Heft",
            'f' => [1, 'x, y', 'plain'],
            'g' => null,
            'h' => true,
            'url' => 'http://example.org/a#frag',
            'list' => ['same indent'],
        ], (new yaml_reader())->parse($yaml));
    }

    /**
     * @dataProvider unsupported_provider
     */
    public function test_rejects_unsupported(string $yaml): void {
        $this->expectException(\coding_exception::class);
        (new yaml_reader())->parse($yaml);
    }

    public static function unsupported_provider(): array {
        return [
            'block scalar' => ["a: |\n  text"],
            'anchor' => ["a: &x 1"],
            'flow mapping' => ["a: {b: 1}"],
            'bad indentation' => ["a: 1\n   b: 2"],
            'duplicate key' => ["a: 1\na: 2"],
        ];
    }
}
