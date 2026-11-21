<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Registry of available exercise templates.
 *
 * Template ids match the "exercise_generators" entries of the curriculum packs.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\generator;

final class registry {
    /**
     * @var array<string, array{class: class-string<exercise_generator>, topic: string, gradefrom: int, gradeto: int, levels: string[]}>
     */
    private const TEMPLATES = [
        'fraction_numberline' => [
            'class' => fraction_numberline::class,
            'topic' => 'Brüche',
            'gradefrom' => 5,
            'gradeto' => 6,
            'levels' => ['AFB I'],
        ],
        'fraction_compare' => [
            'class' => fraction_compare::class,
            'topic' => 'Brüche',
            'gradefrom' => 6,
            'gradeto' => 7,
            'levels' => ['AFB I', 'AFB II'],
        ],
        'integer_numberline' => [
            'class' => integer_numberline::class,
            'topic' => 'Rationale Zahlen',
            'gradefrom' => 5,
            'gradeto' => 7,
            'levels' => ['AFB I'],
        ],
        'rational_addition' => [
            'class' => rational_addition::class,
            'topic' => 'Rationale Zahlen',
            'gradefrom' => 7,
            'gradeto' => 8,
            'levels' => ['AFB I', 'AFB II'],
        ],
        'sign_error_analysis' => [
            'class' => sign_error_analysis::class,
            'topic' => 'Rationale Zahlen',
            'gradefrom' => 7,
            'gradeto' => 8,
            'levels' => ['AFB II', 'AFB III'],
        ],
        'linear_equation' => [
            'class' => linear_equation::class,
            'topic' => 'Gleichungen',
            'gradefrom' => 7,
            'gradeto' => 8,
            'levels' => ['AFB I', 'AFB II'],
        ],
    ];

    /**
     * @return string[] all template ids
     */
    public static function templateids(): array {
        return array_keys(self::TEMPLATES);
    }

    /**
     * @return string[] distinct topics, sorted
     */
    public static function topics(): array {
        $topics = array_unique(array_column(self::TEMPLATES, 'topic'));
        sort($topics);
        return $topics;
    }

    public static function exists(string $templateid): bool {
        return isset(self::TEMPLATES[$templateid]);
    }

    public static function get(string $templateid): exercise_generator {
        if (!self::exists($templateid)) {
            throw new \coding_exception('Unknown exercise template: ' . $templateid);
        }
        $class = self::TEMPLATES[$templateid]['class'];
        return new $class();
    }

    /**
     * Templates matching the activity settings.
     *
     * Filters are relaxed step by step (level, then grade, then topic) so that
     * an activity never ends up without exercises. If $allowed is given (template
     * ids linked to a curriculum competency), only those are considered as long
     * as at least one of them is known to this plugin.
     *
     * @param string[]|null $allowed
     * @return string[]
     */
    public static function find(?string $topic, ?int $grade, ?string $level = null, ?array $allowed = null): array {
        $pool = self::templateids();
        if ($allowed !== null) {
            $known = array_values(array_intersect($pool, $allowed));
            if ($known) {
                $pool = $known;
                // The competency already defines the content; topic/grade must not exclude it.
                $topic = null;
                $grade = null;
            }
        }

        $filter = static function (bool $usetopic, bool $usegrade, bool $uselevel) use ($pool, $topic, $grade, $level): array {
            return array_values(array_filter($pool, static function (string $id) use ($usetopic, $usegrade, $uselevel, $topic, $grade, $level): bool {
                $meta = self::TEMPLATES[$id];
                if ($usetopic && $topic !== null && $topic !== '' && $meta['topic'] !== $topic) {
                    return false;
                }
                if ($usegrade && $grade !== null && ($grade < $meta['gradefrom'] || $grade > $meta['gradeto'])) {
                    return false;
                }
                if ($uselevel && $level !== null && $level !== '' && !in_array($level, $meta['levels'], true)) {
                    return false;
                }
                return true;
            }));
        };

        return $filter(true, true, true)
            ?: $filter(true, true, false)
            ?: $filter(true, false, false)
            ?: $pool;
    }
}
