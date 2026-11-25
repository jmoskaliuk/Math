<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Builds a Bloom learning package from generated exercises.
 *
 * The result follows the slide schema of Bloom (types "info", "mc", "input",
 * "result") and can be sent to Bloom's create_slides as it is. The typical
 * misconceptions of each exercise become multiple-choice distractors whose
 * feedback explains exactly that mistake.
 *
 * Pure logic: texts come from a translator callback, so the builder works in
 * Moodle (get_string) as well as in tests.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\bloom;

use mod_mathetrainer\generator\registry;
use mod_mathetrainer\generator\task_generator;
use mod_mathetrainer\local\fraction;
use mod_mathetrainer\local\numberline_svg;
use mod_mathetrainer\local\random;

/**
 * Turns exercise templates into a Bloom package (concept, slides, assets).
 */
final class package_builder {
    /** Package format identifier. */
    public const FORMAT = 'mathetrainer-bloom-package';

    /** Package format version. */
    public const VERSION = 1;

    /** All numeric exercises as short-answer input slides. */
    public const STYLE_INPUT = 'input';

    /** All exercises as multiple choice with misconception distractors. */
    public const STYLE_MC = 'mc';

    /** Alternate input and multiple choice. */
    public const STYLE_MIXED = 'mixed';

    /** Level in Bloom's taxonomy per template (Bloom slide field "bloom"). */
    private const TAXONOMY = [
        'order_of_operations' => 'anwenden',
        'decimal_convert' => 'anwenden',
        'fraction_numberline' => 'verstehen',
        'fraction_compare' => 'verstehen',
        'integer_numberline' => 'verstehen',
        'rational_addition' => 'anwenden',
        'sign_error_analysis' => 'analysieren',
        'linear_equation' => 'anwenden',
    ];

    /** @var callable(string, mixed=): string */
    private $str;

    /**
     * Constructor.
     *
     * @param callable $str translator: fn(string $key, mixed $a = null): string for mod_mathetrainer strings
     */
    public function __construct(callable $str) {
        $this->str = $str;
    }

    /**
     * Builds a package.
     *
     * @param string[] $templateids exercise types, used in turn
     * @param int $count number of exercises
     * @param int $seed base seed; the same seed gives the same package
     * @param string $style one of the STYLE_* constants
     * @param array $meta title, description, grade, competency (all optional)
     * @return array package with keys format, version, project, concept, slides, assets, source
     */
    public function build(array $templateids, int $count, int $seed, string $style = self::STYLE_MIXED, array $meta = []): array {
        $templateids = array_values(array_filter($templateids, [registry::class, 'exists']));
        if (!$templateids) {
            throw new \coding_exception('No known exercise templates given.');
        }
        $count = max(1, min(30, $count));

        $generator = new task_generator();
        $slides = [];
        $assets = [];
        $items = [];

        $slides[] = $this->intro_slide($templateids, $meta);
        for ($n = 1; $n <= $count; $n++) {
            $templateid = $templateids[($n - 1) % count($templateids)];
            $itemseed = $this->item_seed($seed, $n);
            $exercise = $generator->generate($templateid, $itemseed);
            $usemc = $exercise['payload']['type'] === 'choice'
                || $style === self::STYLE_MC
                || ($style === self::STYLE_MIXED && $n % 2 === 0);
            $slide = $usemc
                ? $this->mc_slide($exercise, $n, $itemseed)
                : $this->input_slide($exercise, $n);

            if (!empty($exercise['payload']['numberline'])) {
                $key = 'numberline-' . $n;
                $assets[$key] = [
                    'type' => 'image/svg+xml',
                    'content' => numberline_svg::render($exercise['payload']['numberline'], $this->s('numberline_alt')),
                ];
                $slide['image'] = ['media' => 'asset:' . $key, 'alt' => $this->s('numberline_alt')];
            }
            $slide['bloom'] = self::TAXONOMY[$templateid] ?? 'anwenden';
            $slide['chapter'] = $this->s('template_' . $templateid);
            // Traceability: which generator and seed produced this slide (for regenerating variants).
            $slide['mathetrainer'] = ['templateid' => $templateid, 'seed' => $itemseed];
            $slides[] = $slide;
            $items[] = ['templateid' => $templateid, 'seed' => $itemseed];
        }
        $slides[] = [
            'type' => 'result',
            'title' => $this->s('bloom_result_title'),
            'passText' => $this->s('bloom_result_pass'),
            'failText' => $this->s('bloom_result_fail'),
        ];

        $title = (string)($meta['title'] ?? implode(', ', array_map(fn($t) => $this->s('template_' . $t), $templateids)));

        return [
            'format' => self::FORMAT,
            'version' => self::VERSION,
            'project' => [
                'title' => $title,
                'description' => (string)($meta['description'] ?? $this->s('bloom_description', $count)),
            ],
            'concept' => $this->concept($templateids, $meta),
            'slides' => $slides,
            'assets' => $assets,
            'source' => [
                'generator' => 'mod_mathetrainer',
                'templates' => $templateids,
                'seed' => $seed,
                'style' => $style,
                'items' => $items,
            ],
        ];
    }

    /**
     * Deterministic, well spread seed for exercise number $n of a package.
     *
     * @param int $seed package seed
     * @param int $n exercise number
     * @return int
     */
    private function item_seed(int $seed, int $n): int {
        $rng = new random($seed * 31 + $n);
        return $rng->int(1, 2147483646);
    }

    /**
     * Opening info slide with what the package practises and a few tips.
     *
     * @param string[] $templateids
     * @param array $meta
     * @return array
     */
    private function intro_slide(array $templateids, array $meta): array {
        $points = [];
        foreach ($templateids as $templateid) {
            $points[] = ['title' => $this->s('template_' . $templateid), 'text' => $this->s('keypoint_' . $templateid)];
        }
        return [
            'type' => 'info',
            'title' => (string)($meta['title'] ?? $this->s('bloom_intro_title')),
            'body' => $this->s('bloom_intro_body'),
            'points' => $points,
            'boxes' => [['kind' => 'tip', 'text' => $this->s('bloom_intro_tip')]],
            'callout' => '',
        ];
    }

    /**
     * Learning concept for Bloom (audience, goals, key points).
     *
     * @param string[] $templateids
     * @param array $meta
     * @return array
     */
    private function concept(array $templateids, array $meta): array {
        $goals = [];
        $keypoints = [];
        foreach ($templateids as $i => $templateid) {
            $goals[] = [
                'id' => 'L' . ($i + 1),
                'text' => $this->s('goal_' . $templateid),
                'bloom' => self::TAXONOMY[$templateid] ?? 'anwenden',
            ];
            $keypoints[] = ['id' => 'K' . ($i + 1), 'text' => $this->s('keypoint_' . $templateid)];
        }
        $grade = isset($meta['grade']) ? (int)$meta['grade'] : null;
        return [
            'audience' => [
                'context' => 'Schule',
                'description' => $grade ? $this->s('bloom_audience_grade', $grade) : $this->s('bloom_audience'),
            ],
            'goals' => $goals,
            'keyPoints' => $keypoints,
            'tone' => 'locker',
            'address' => 'du',
            'notes' => isset($meta['competency']) ? $this->s('bloom_competency_note', (string)$meta['competency']) : '',
        ];
    }

    /**
     * Short-answer slide; accepts the usual notations of the correct answer.
     *
     * @param array $exercise generated exercise
     * @param int $n exercise number
     * @return array
     */
    private function input_slide(array $exercise, int $n): array {
        $expected = $exercise['expected'];
        return [
            'type' => 'input',
            'title' => $this->s('exerciseno', $n),
            'mode' => 'short',
            'prompt' => $this->prompt($exercise),
            'answers' => self::answer_variants($exercise),
            'scored' => true,
            'storage' => 'lms',
            'feedbackCorrect' => $this->s('feedback_correct'),
            'feedbackIncorrect' => $this->s('correctansweris', self::display($expected['display'] ?? $expected['answer'])),
        ];
    }

    /**
     * Multiple-choice slide: correct answer plus misconception distractors with explaining feedback.
     *
     * @param array $exercise generated exercise
     * @param int $n exercise number
     * @param int $seed seed for the option order
     * @return array
     */
    private function mc_slide(array $exercise, int $n, int $seed): array {
        $payload = $exercise['payload'];
        $expected = $exercise['expected'];
        $answer = (string)($expected['display'] ?? $expected['answer']);

        $options = [['text' => self::display($answer), 'correct' => true, 'feedback' => $this->s('feedback_correct')]];
        $used = [self::normalise($answer, $payload) => true];

        $candidates = [];
        if ($payload['type'] === 'choice') {
            foreach ((array)$payload['options'] as $option) {
                $candidates[(string)$option] = (string)($expected['diagnostics'][$option] ?? 'wrong');
            }
        } else {
            foreach ((array)$expected['diagnostics'] as $wrong => $category) {
                $candidates[self::in_format((string)$wrong, $payload)] = (string)$category;
            }
            // Ensure at least three options with near values if diagnostics are scarce.
            $value = fraction::parse((string)$expected['answer']);
            foreach ([1, -1, 2, 10] as $delta) {
                if (count($candidates) >= 3 || $value === null) {
                    break;
                }
                $near = self::in_format((string)$value->add(new fraction($delta)), $payload);
                $candidates[$near] ??= 'wrong';
            }
        }

        foreach ($candidates as $text => $category) {
            $key = self::normalise((string)$text, $payload);
            if (isset($used[$key]) || count($options) >= 4) {
                continue;
            }
            $used[$key] = true;
            $options[] = [
                'text' => self::display((string)$text),
                'correct' => false,
                'feedback' => $this->s('error_' . $category),
            ];
        }

        // Deterministic shuffle so the correct answer is not always first.
        $rng = new random($seed);
        for ($i = count($options) - 1; $i > 0; $i--) {
            $j = $rng->int(0, $i);
            [$options[$i], $options[$j]] = [$options[$j], $options[$i]];
        }

        return [
            'type' => 'mc',
            'title' => $this->s('exerciseno', $n),
            'question' => $this->prompt($exercise),
            'scored' => true,
            'options' => $options,
            'feedbackCorrect' => $this->s('feedback_correct'),
            'feedbackIncorrect' => $this->s('error_wrong'),
        ];
    }

    /**
     * Prompt text of an exercise.
     *
     * @param array $exercise
     * @return string
     */
    private function prompt(array $exercise): string {
        $payload = $exercise['payload'];
        return $this->s((string)$payload['promptkey'], (string)($payload['expression'] ?? ''));
    }

    /**
     * Accepted spellings of the correct answer for a short-answer slide.
     *
     * @param array $exercise
     * @return string[]
     */
    public static function answer_variants(array $exercise): array {
        $payload = $exercise['payload'];
        $expected = $exercise['expected'];
        $answer = (string)$expected['answer'];
        $variants = [];

        if (($payload['format'] ?? null) !== 'decimal') {
            $variants[] = $answer;
        }
        if (($payload['format'] ?? null) !== 'fraction') {
            $decimal = self::decimal((string)$answer);
            if ($decimal !== null) {
                $variants[] = $decimal;
                $variants[] = str_replace('.', ',', $decimal);
            }
            if (isset($expected['display'])) {
                $variants[] = (string)$expected['display'];
            }
        }

        $all = [];
        foreach ($variants as $variant) {
            $all[] = $variant;
            $all[] = str_replace('-', "\u{2212}", $variant);
        }
        return array_values(array_unique($all));
    }

    /**
     * Exact decimal notation of a canonical value if it terminates within 4 places, else null.
     *
     * @param string $value canonical value ("-3/4", "5")
     * @return string|null
     */
    private static function decimal(string $value): ?string {
        $f = fraction::parse($value);
        if ($f === null || $f->is_integer()) {
            return null;
        }
        for ($digits = 1; $digits <= 4; $digits++) {
            $scale = 10 ** $digits;
            if ($scale % $f->denominator === 0) {
                $scaled = intdiv(abs($f->numerator) * $scale, $f->denominator);
                $text = intdiv($scaled, $scale) . '.' . rtrim(str_pad((string)($scaled % $scale), $digits, '0', STR_PAD_LEFT), '0');
                return ($f->numerator < 0 ? '-' : '') . $text;
            }
        }
        return null;
    }

    /**
     * Writes a canonical value in the notation the exercise asks for.
     *
     * @param string $value canonical value
     * @param array $payload exercise payload
     * @return string
     */
    private static function in_format(string $value, array $payload): string {
        if (($payload['format'] ?? null) === 'decimal') {
            $decimal = self::decimal($value);
            return $decimal === null ? $value : str_replace('.', ',', $decimal);
        }
        return $value;
    }

    /**
     * Comparable key of an answer (value for numbers, text otherwise).
     *
     * @param string $text
     * @param array $payload
     * @return string
     */
    private static function normalise(string $text, array $payload): string {
        if ($payload['type'] === 'choice') {
            return $text;
        }
        $f = fraction::parse($text);
        return $f === null ? $text : (string)$f;
    }

    /**
     * Display form with typographic minus.
     *
     * @param string $text
     * @return string
     */
    private static function display(string $text): string {
        return str_replace('-', "\u{2212}", $text);
    }

    /**
     * Translated string.
     *
     * @param string $key
     * @param mixed $a
     * @return string
     */
    private function s(string $key, $a = null): string {
        return (string)($this->str)($key, $a);
    }
}
