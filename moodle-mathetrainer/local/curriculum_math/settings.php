<?php
// This file is part of the Moodle plugin local_curriculum_math.

/**
 * Admin settings page for local_curriculum_math.
 *
 * @package    local_curriculum_math
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_curriculum_math',
        get_string('pluginname', 'local_curriculum_math')
    );
    $ADMIN->add('localplugins', $settings);
}
