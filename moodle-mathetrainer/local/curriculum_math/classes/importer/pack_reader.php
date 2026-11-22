<?php
declare(strict_types=1);

namespace local_curriculum_math\importer;

use coding_exception;

final class pack_reader {
    /**
     * @return array<string, mixed>
     */
    public function read_file(string $filepath): array {
        if (!is_file($filepath)) {
            throw new coding_exception('Curriculum file not found: ' . $filepath);
        }

        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        if ($ext === 'json') {
            $content = file_get_contents($filepath) ?: '';
            $decoded = json_decode($content, true);
            if (!is_array($decoded)) {
                throw new coding_exception('Invalid JSON curriculum pack: ' . $filepath);
            }
            return $decoded;
        }

        if ($ext === 'yaml' || $ext === 'yml') {
            // Prefer ext-yaml if installed; otherwise use the built-in reader for the pack subset.
            $decoded = function_exists('yaml_parse_file')
                ? yaml_parse_file($filepath)
                : (new yaml_reader())->parse(file_get_contents($filepath) ?: '');
            if (!is_array($decoded)) {
                throw new coding_exception('Invalid YAML curriculum pack: ' . $filepath);
            }
            return $decoded;
        }

        throw new coding_exception('Unsupported curriculum file extension: ' . $ext);
    }
}
