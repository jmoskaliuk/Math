<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_curriculum_math',
        get_string('pluginname', 'curriculum_math')
    );
    $ADMIN->add('localplugins', $settings);
}
