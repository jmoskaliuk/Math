<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('modsettingmathetrainer', get_string('pluginname', 'mathetrainer'));
    $ADMIN->add('modsettings', $settings);
}
