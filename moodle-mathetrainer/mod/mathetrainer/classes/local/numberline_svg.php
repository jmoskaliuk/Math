<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Renders the "numberline" part of an item payload as accessible inline SVG.
 *
 * Uses currentColor so the drawing follows the theme's text colour.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

/**
 * Renders number lines as inline SVG.
 */
final class numberline_svg {
    /** @var int Width of the drawing */
    private const WIDTH = 520;
    /** @var int Height of the drawing */
    private const HEIGHT = 90;
    /** @var int Horizontal distance of the first and last tick from the edge */
    private const MARGIN = 30;
    /** @var int Vertical position of the axis */
    private const AXIS_Y = 45;

    /**
     * Renders a number line with labelled ticks and a marked point.
     *
     * @param array $line array{divisions: int, labels: array<int|string, string>, marked: int}
     * @param string $alttext accessible description
     * @return string SVG markup
     */
    public static function render(array $line, string $alttext): string {
        $divisions = max(1, (int)$line['divisions']);
        $marked = (int)$line['marked'];
        $span = self::WIDTH - 2 * self::MARGIN;
        $x = static fn(int $i): float => round(self::MARGIN + $span * $i / $divisions, 2);
        $y = self::AXIS_Y;

        $parts = [];
        // Axis with arrow head.
        $parts[] = sprintf(
            '<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="currentColor" stroke-width="2"/>',
            self::MARGIN - 15,
            $y,
            self::WIDTH - 8,
            $y
        );
        $parts[] = sprintf('<path d="M%d %d l-10 -5 v10 z" fill="currentColor"/>', self::WIDTH - 4, $y);

        $labels = [];
        foreach ($line['labels'] as $index => $text) {
            $labels[(int)$index] = (string)$text;
        }

        for ($i = 0; $i <= $divisions; $i++) {
            $long = isset($labels[$i]);
            $parts[] = sprintf(
                '<line x1="%s" y1="%d" x2="%s" y2="%d" stroke="currentColor" stroke-width="%s"/>',
                $x($i),
                $y - ($long ? 10 : 6),
                $x($i),
                $y + ($long ? 10 : 6),
                $long ? '2' : '1.2'
            );
            if ($long) {
                $parts[] = sprintf(
                    '<text x="%s" y="%d" text-anchor="middle" font-size="16" fill="currentColor">%s</text>',
                    $x($i),
                    $y + 30,
                    htmlspecialchars($labels[$i], ENT_QUOTES | ENT_XML1)
                );
            }
        }

        // Marked point with a downward arrow.
        $mx = $x($marked);
        $parts[] = sprintf('<circle cx="%s" cy="%d" r="6" fill="#d9480f"/>', $mx, $y);
        $parts[] = sprintf(
            '<path d="M%s %d v14 m-5 -5 l5 5 l5 -5" stroke="#d9480f" stroke-width="2" fill="none"/>',
            $mx,
            $y - 34
        );

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="100%%" style="max-width:%dpx" '
                . 'role="img" aria-label="%s" class="mathetrainer-numberline">%s</svg>',
            self::WIDTH,
            self::HEIGHT,
            self::WIDTH,
            htmlspecialchars($alttext, ENT_QUOTES),
            implode('', $parts)
        );
    }
}
