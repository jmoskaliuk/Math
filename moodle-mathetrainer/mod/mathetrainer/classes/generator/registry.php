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
     * @var array<string, array{class: class-string<exercise_generator>, topic: string, gradefrom: int, gradeto: int}>
     */
    private const TEMPLATES = [
        'fraction_compare' => [
            'class' => fraction_compare::class,
            'topic' => 'Brüche',
            'gradefrom' => 6,
            'gradeto' => 7,
        ],
        'rational_addition' => [
            'class' => rational_addition::class,
            'topic' => 'Rationale Zahlen',
            'gradefrom' => 7,
            'gradeto' => 8,
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
     * Templates matching the activity settings. Falls back step by step
     * (topic only, then everything) so that an activity never ends up empty.
     *
     * @return string[]
     */
    public static function find(?string $topic, ?int $grade): array {
        $matches = static function (bool $usegrade) use ($topic, $grade): array {
            $ids = [];
            foreach (self::TEMPLATES as $id => $meta) {
                if ($topic !== null && $topic !== '' && $meta['topic'] !== $topic) {
                    continue;
                }
                if ($usegrade && $grade !== null && ($grade < $meta['gradefrom'] || $grade > $meta['gradeto'])) {
                    continue;
                }
                $ids[] = $id;
            }
            return $ids;
        };

        return $matches(true) ?: $matches(false) ?: self::templateids();
    }
}
