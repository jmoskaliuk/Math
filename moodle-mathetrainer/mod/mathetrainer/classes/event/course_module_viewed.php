<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * The mod_mathetrainer course module viewed event.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

namespace mod_mathetrainer\event;

/**
 * Event triggered when a mathetrainer activity is viewed.
 */
class course_module_viewed extends \core\event\course_module_viewed {
    /**
     * Sets the object table of the event.
     */
    protected function init() {
        $this->data['objecttable'] = 'mathetrainer';
        parent::init();
    }

    /**
     * Mapping of the object id for backup and restore.
     *
     * @return array
     */
    public static function get_objectid_mapping() {
        return ['db' => 'mathetrainer', 'restore' => 'mathetrainer'];
    }
}
