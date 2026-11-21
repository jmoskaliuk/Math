<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * The mod_mathetrainer course module viewed event.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

namespace mod_mathetrainer\event;

defined('MOODLE_INTERNAL') || die();

class course_module_viewed extends \core\event\course_module_viewed {
    protected function init() {
        $this->data['objecttable'] = 'mathetrainer';
        parent::init();
    }

    public static function get_objectid_mapping() {
        return ['db' => 'mathetrainer', 'restore' => 'mathetrainer'];
    }
}
