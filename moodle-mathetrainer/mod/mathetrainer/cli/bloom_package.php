<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Generates a Bloom learning package (JSON) from exercise templates.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognized] = cli_get_params([
    'help' => false,
    'templates' => '',
    'topic' => '',
    'grade' => 0,
    'level' => '',
    'count' => 8,
    'seed' => 0,
    'style' => \mod_mathetrainer\bloom\package_builder::STYLE_MIXED,
    'title' => '',
    'lang' => 'de',
    'output' => '',
], [
    'h' => 'help',
    'o' => 'output',
]);

if ($options['help'] || ($options['templates'] === '' && $options['topic'] === '')) {
    echo "Generates a Bloom learning package (JSON) from maths trainer exercises.\n\n";
    echo "Options:\n";
    echo "  --templates=a,b   Exercise types (" . implode(', ', \mod_mathetrainer\generator\registry::templateids()) . ")\n";
    echo "  --topic=TEXT      Or select by topic (" . implode(', ', \mod_mathetrainer\generator\registry::topics()) . ")\n";
    echo "  --grade=N         Grade for the selection by topic and for the concept\n";
    echo "  --level='AFB II'  Requirement level for the selection by topic\n";
    echo "  --count=N         Number of exercises (1-30, default 8)\n";
    echo "  --seed=N          Same seed = same package (default: random)\n";
    echo "  --style=mixed     input | mc | mixed\n";
    echo "  --title=TEXT      Project title\n";
    echo "  --lang=de         Language of the texts\n";
    echo "  -o, --output=FILE Write to file instead of stdout\n\n";
    echo "Example:\n";
    echo "  php mod/mathetrainer/cli/bloom_package.php --topic='Brüche' --grade=6 --count=10 -o=brueche.json\n";
    exit(0);
}

$templates = $options['templates'] !== ''
    ? array_map('trim', explode(',', $options['templates']))
    : \mod_mathetrainer\generator\registry::find(
        $options['topic'],
        $options['grade'] ? (int)$options['grade'] : null,
        $options['level'] ?: null
    );
foreach ($templates as $template) {
    if (!\mod_mathetrainer\generator\registry::exists($template)) {
        cli_error('Unknown exercise type: ' . $template);
    }
}

force_current_language($options['lang']);
$builder = new \mod_mathetrainer\bloom\package_builder(
    static fn(string $key, $a = null): string => get_string($key, 'mathetrainer', $a)
);
$meta = [];
if ($options['title'] !== '') {
    $meta['title'] = $options['title'];
}
if ($options['grade']) {
    $meta['grade'] = (int)$options['grade'];
}
$package = $builder->build(
    $templates,
    (int)$options['count'],
    (int)$options['seed'] ?: random_int(1, 999999),
    $options['style'],
    $meta
);

$json = json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($options['output'] !== '') {
    file_put_contents($options['output'], $json . "\n");
    cli_writeln('Written ' . count($package['slides']) . ' slides to ' . $options['output']);
} else {
    echo $json, "\n";
}
