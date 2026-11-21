<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Deterministic pseudo random number generator (xorshift32).
 *
 * The same seed always yields the same sequence, independent of PHP's global
 * random state. This keeps generated exercises reproducible and auditable.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

declare(strict_types=1);

namespace mod_mathetrainer\local;

final class random {
    private const MASK = 0xFFFFFFFF;

    private int $state;

    public function __construct(int $seed) {
        $state = $seed & self::MASK;
        // Xorshift must never be in state 0.
        $this->state = $state === 0 ? 0x9E3779B9 : $state;
        // Warm up so that neighbouring seeds diverge quickly.
        for ($i = 0; $i < 8; $i++) {
            $this->next();
        }
    }

    /**
     * Next raw 32 bit unsigned value.
     */
    public function next(): int {
        $x = $this->state;
        $x ^= ($x << 13) & self::MASK;
        $x ^= $x >> 17;
        $x ^= ($x << 5) & self::MASK;
        $this->state = $x & self::MASK;
        return $this->state;
    }

    /**
     * Integer in the closed interval [$min, $max].
     */
    public function int(int $min, int $max): int {
        if ($max < $min) {
            throw new \coding_exception('random::int(): max < min');
        }
        return $min + ($this->next() % ($max - $min + 1));
    }

    /**
     * Integer in [$min, $max] excluding zero.
     */
    public function nonzero_int(int $min, int $max): int {
        do {
            $value = $this->int($min, $max);
        } while ($value === 0);
        return $value;
    }

    /**
     * Random element of a non-empty list.
     *
     * @template T
     * @param array<int, T> $items
     * @return T
     */
    public function pick(array $items): mixed {
        $items = array_values($items);
        return $items[$this->int(0, count($items) - 1)];
    }

    /**
     * True with probability $numerator / $denominator.
     */
    public function chance(int $numerator, int $denominator): bool {
        return $this->int(1, $denominator) <= $numerator;
    }
}
