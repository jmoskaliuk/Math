<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * CLI importer for local_curriculum_math packs.
 *
 * Imports one pack (--file) or all packs in a directory tree (--dir), KMK packs first.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params([
    'help' => false,
    'file' => null,
    'dir' => null,
], [
    'h' => 'help',
    'f' => 'file',
    'd' => 'dir',
]);

if ($options['help'] || (empty($options['file']) && empty($options['dir']))) {
    echo "Import curriculum packs into local_curriculum_math\n\n";
    echo "Re-importing a pack with the same framework id and version updates it.\n\n";
    echo "Options:\n";
    echo "  -f, --file     Path to one YAML or JSON pack\n";
    echo "  -d, --dir      Directory: imports all *.yaml, *.yml and *.json packs in it and its\n";
    echo "                 subdirectories, KMK packs first\n";
    echo "  -h, --help     Show this help\n\n";
    echo "Example:\n";
    echo "  php local/curriculum_math/cli/import_curriculum.php --dir=/path/to/curriculum-packs\n";
    exit(0);
}

\core\session\manager::write_close();

$files = [];
if (!empty($options['file'])) {
    $files[] = (string)$options['file'];
}
if (!empty($options['dir'])) {
    $dir = rtrim((string)$options['dir'], '/');
    if (!is_dir($dir)) {
        cli_error('Directory not found: ' . $dir);
    }
    $found = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (preg_match('/\.(ya?ml|json)$/i', $file->getFilename())) {
            $found[] = $file->getPathname();
        }
    }
    // KMK first so that state packs can refer to it, then alphabetical.
    usort($found, static function (string $a, string $b): int {
        $ka = str_contains(strtolower($a), '/kmk/') ? 0 : 1;
        $kb = str_contains(strtolower($b), '/kmk/') ? 0 : 1;
        return [$ka, $a] <=> [$kb, $b];
    });
    $files = array_merge($files, $found);
}

$reader = new \local_curriculum_math\importer\pack_reader();
$validator = new \local_curriculum_math\importer\validator();
$service = new \local_curriculum_math\importer\import_service($reader, $validator);

$errors = 0;
foreach ($files as $file) {
    try {
        $frameworkid = $service->import_path($file);
        echo "OK     {$file} (framework record id {$frameworkid})\n";
    } catch (Throwable $e) {
        $errors++;
        echo "ERROR  {$file}: " . $e->getMessage() . "\n";
    }
}

exit($errors ? 1 : 0);
