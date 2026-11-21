<?php
// This file is part of the Moodle plugin mod_mathetrainer.

/**
 * Activity settings form.
 *
 * @package    mod_mathetrainer
 * @license    MIT
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_mathetrainer_mod_form extends moodleform_mod {
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'curriculumhdr', get_string('curriculumsettings', 'mathetrainer'));

        $states = [
            'NRW' => get_string('state_nrw', 'mathetrainer'),
            'BW' => get_string('state_bw', 'mathetrainer'),
        ];
        $mform->addElement('select', 'state', get_string('state', 'mathetrainer'), $states);
        $mform->setDefault('state', 'NRW');

        $grades = [];
        for ($g = 1; $g <= 13; $g++) {
            $grades[$g] = get_string('gradelevelvalue', 'mathetrainer', $g);
        }
        $mform->addElement('select', 'gradelevel', get_string('gradelevel', 'mathetrainer'), $grades);
        $mform->setDefault('gradelevel', 7);

        $topics = ['' => get_string('alltopics', 'mathetrainer')];
        foreach (\mod_mathetrainer\generator\registry::topics() as $topic) {
            $topics[$topic] = $topic;
        }
        $mform->addElement('select', 'topic', get_string('topic', 'mathetrainer'), $topics);
        $mform->addHelpButton('topic', 'topic', 'mathetrainer');

        $levels = [
            '' => get_string('any', 'mathetrainer'),
            'AFB I' => 'AFB I',
            'AFB II' => 'AFB II',
            'AFB III' => 'AFB III',
        ];
        $mform->addElement('select', 'requirementlevel', get_string('requirementlevel', 'mathetrainer'), $levels);

        $mform->addElement('text', 'competencyid', get_string('competencyid', 'mathetrainer'), ['size' => 40]);
        $mform->setType('competencyid', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('competencyid', 'competencyid', 'mathetrainer');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
