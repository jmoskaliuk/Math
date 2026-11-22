<?php
declare(strict_types=1);

namespace local_curriculum_math\importer;

use coding_exception;

/**
 * Minimal YAML reader for curriculum packs, used when ext-yaml is not installed
 * (which is the case on most Moodle servers).
 *
 * Supported subset: block mappings and sequences (indented with spaces),
 * sequences of mappings ("- key: value"), flow sequences ("[a, "b"]"),
 * single/double quoted and plain scalars, integers, floats, booleans, null
 * and comments. Anchors, tags, multi-line scalars (| and >) and flow mappings
 * are rejected with an exception instead of being misread.
 */
final class yaml_reader {
    /** @var array<int, array{indent: int, text: string, line: int}> */
    private array $lines = [];
    private int $pos = 0;

    /**
     * @return array<string|int, mixed>
     */
    public function parse(string $yaml): array {
        $this->lines = [];
        $this->pos = 0;

        foreach (preg_split('/\R/', $yaml) as $number => $raw) {
            if (str_contains($raw, "\t") && preg_match('/^\s*\t/', $raw)) {
                throw new coding_exception('YAML: tabs are not allowed for indentation (line ' . ($number + 1) . ')');
            }
            $text = rtrim(self::strip_comment($raw));
            if (trim($text) === '' || trim($text) === '---') {
                continue;
            }
            $indent = strlen($text) - strlen(ltrim($text, ' '));
            $this->lines[] = ['indent' => $indent, 'text' => ltrim($text, ' '), 'line' => $number + 1];
        }

        if (!$this->lines) {
            return [];
        }
        $result = $this->parse_block($this->lines[0]['indent']);
        if ($this->pos < count($this->lines)) {
            $this->fail('unexpected indentation');
        }
        if (!is_array($result)) {
            throw new coding_exception('YAML: document is not a mapping or sequence');
        }
        return $result;
    }

    private function parse_block(int $indent): array {
        return self::is_sequence_item($this->lines[$this->pos]['text'])
            ? $this->parse_sequence($indent)
            : $this->parse_mapping($indent);
    }

    private function parse_mapping(int $indent): array {
        $map = [];
        while ($this->pos < count($this->lines)) {
            $line = $this->lines[$this->pos];
            if ($line['indent'] < $indent) {
                break;
            }
            if ($line['indent'] > $indent) {
                $this->fail('unexpected indentation');
            }
            if (self::is_sequence_item($line['text'])) {
                break;
            }
            if (!preg_match('/^("(?:[^"\\\\]|\\\\.)*"|\'(?:[^\']|\'\')*\'|[^\s"\'#\[\]{},:][^:]*?)\s*:(?:\s+(.*))?$/', $line['text'], $m)) {
                $this->fail('expected "key: value"');
            }
            $key = (string)self::scalar($m[1]);
            $value = $m[2] ?? '';
            if (array_key_exists($key, $map)) {
                $this->fail('duplicate key "' . $key . '"');
            }
            $this->pos++;

            if ($value !== '') {
                $map[$key] = $this->inline_value($value);
                continue;
            }
            $next = $this->lines[$this->pos] ?? null;
            if ($next !== null && $next['indent'] > $indent) {
                $map[$key] = $this->parse_block($next['indent']);
            } else if ($next !== null && $next['indent'] === $indent && self::is_sequence_item($next['text'])) {
                // Sequence written at the same indentation as its key.
                $map[$key] = $this->parse_sequence($indent);
            } else {
                $map[$key] = null;
            }
        }
        return $map;
    }

    private function parse_sequence(int $indent): array {
        $list = [];
        while ($this->pos < count($this->lines)) {
            $line = $this->lines[$this->pos];
            if ($line['indent'] !== $indent || !self::is_sequence_item($line['text'])) {
                if ($line['indent'] > $indent) {
                    $this->fail('unexpected indentation');
                }
                break;
            }
            $rest = ltrim(substr($line['text'], 1), ' ');
            if ($rest === '') {
                $this->pos++;
                $next = $this->lines[$this->pos] ?? null;
                $list[] = ($next !== null && $next['indent'] > $indent) ? $this->parse_block($next['indent']) : null;
                continue;
            }
            $offset = strlen($line['text']) - strlen($rest);
            if (self::is_sequence_item($rest) || preg_match('/^[^\s"\'\[{][^:]*:(\s|$)|^"[^"]*"\s*:(\s|$)/', $rest)) {
                // Nested block starting on the item line: treat the rest as its first line.
                $this->lines[$this->pos] = ['indent' => $indent + $offset, 'text' => $rest, 'line' => $line['line']];
                $list[] = $this->parse_block($indent + $offset);
            } else {
                $this->pos++;
                $list[] = $this->inline_value($rest);
            }
        }
        return $list;
    }

    private function inline_value(string $value): mixed {
        $value = trim($value);
        if ($value === '|' || $value === '>' || preg_match('/^[|>][+-]?\d*$/', $value)) {
            $this->fail('multi-line scalars are not supported');
        }
        if ($value[0] === '&' || $value[0] === '*' || $value[0] === '!') {
            $this->fail('anchors, aliases and tags are not supported');
        }
        if ($value[0] === '{') {
            $this->fail('flow mappings are not supported');
        }
        if ($value[0] === '[') {
            if (!str_ends_with($value, ']')) {
                $this->fail('unterminated flow sequence');
            }
            return array_map([self::class, 'scalar'], self::split_flow(substr($value, 1, -1)));
        }
        return self::scalar($value);
    }

    /**
     * @return string[]
     */
    private static function split_flow(string $inner): array {
        if (trim($inner) === '') {
            return [];
        }
        preg_match_all('/\s*("(?:[^"\\\\]|\\\\.)*"|\'(?:[^\']|\'\')*\'|[^,\[\]{}]+?)\s*(?:,|$)/', $inner, $m);
        return $m[1];
    }

    private static function scalar(string $text): mixed {
        $text = trim($text);
        if ($text === '') {
            return null;
        }
        if ($text[0] === '"') {
            $decoded = json_decode($text);
            if (!is_string($decoded)) {
                throw new coding_exception('YAML: invalid double-quoted string ' . $text);
            }
            return $decoded;
        }
        if ($text[0] === "'") {
            if (strlen($text) < 2 || !str_ends_with($text, "'")) {
                throw new coding_exception('YAML: invalid single-quoted string ' . $text);
            }
            return str_replace("''", "'", substr($text, 1, -1));
        }
        $lower = strtolower($text);
        if (in_array($lower, ['null', '~'], true)) {
            return null;
        }
        if (in_array($lower, ['true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array($lower, ['false', 'no', 'off'], true)) {
            return false;
        }
        if (preg_match('/^[-+]?(0|[1-9]\d*)$/', $text)) {
            return (int)$text;
        }
        if (preg_match('/^[-+]?(\d+\.\d*|\.\d+)([eE][-+]?\d+)?$/', $text)) {
            return (float)$text;
        }
        return $text;
    }

    private static function is_sequence_item(string $text): bool {
        return $text === '-' || str_starts_with($text, '- ');
    }

    /**
     * Removes a trailing comment (" #...") that is not inside quotes.
     */
    private static function strip_comment(string $line): string {
        $quote = null;
        $length = strlen($line);
        for ($i = 0; $i < $length; $i++) {
            $c = $line[$i];
            if ($quote !== null) {
                if ($c === '\\' && $quote === '"') {
                    $i++;
                } else if ($c === $quote) {
                    $quote = null;
                }
                continue;
            }
            // A quote only starts a quoted scalar at a value position, not inside plain text (e.g. "Schüler's").
            if (($c === '"' || $c === "'") && ($i === 0 || strpbrk($line[$i - 1], " [,:-") !== false)) {
                $quote = $c;
            } else if ($c === '#' && ($i === 0 || $line[$i - 1] === ' ')) {
                return substr($line, 0, $i);
            }
        }
        return $line;
    }

    private function fail(string $message): never {
        $line = $this->lines[$this->pos]['line'] ?? '?';
        throw new coding_exception('YAML: ' . $message . ' (line ' . $line . ')');
    }
}
