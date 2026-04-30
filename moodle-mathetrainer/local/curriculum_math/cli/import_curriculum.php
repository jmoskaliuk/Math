<?php
// CLI importer for local_curriculum_math packs.
define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params([
    'help' => false,
    'file' => null,
], [
    'h' => 'help',
    'f' => 'file',
]);

if ($options['help'] || empty($options['file'])) {
    echo "Import curriculum pack into local_curriculum_math\n\n";
    echo "Options:\n";
    echo "  -f, --file     Path to YAML or JSON pack\n";
    echo "  -h, --help     Show this help\n";
    exit(0);
}

\core\session\manager::write_close();

$reader = new \local_curriculum_math\importer\pack_reader();
$validator = new \local_curriculum_math\importer\validator();
$service = new \local_curriculum_math\importer\import_service($reader, $validator);

$frameworkid = $service->import_path((string)$options['file']);

echo "Imported framework record id: {$frameworkid}\n";
