<?php

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_mathetrainer_mod_form extends moodleform_mod {
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('select', 'state', get_string('state', 'mathetrainer'), [
            'NRW' => 'NRW',
            'BW' => 'Baden-Württemberg',
        ]);
        $mform->setDefault('state', 'NRW');

        $mform->addElement('text', 'schoolform', get_string('schoolform', 'mathetrainer'));
        $mform->setType('schoolform', PARAM_TEXT);

        $mform->addElement('select', 'gradelevel', get_string('gradelevel', 'mathetrainer'), [5 => '5', 6 => '6', 7 => '7']);
        $mform->setDefault('gradelevel', 5);

        $mform->addElement('text', 'topic', get_string('topic', 'mathetrainer'));
        $mform->setType('topic', PARAM_TEXT);

        $mform->addElement('text', 'competencyid', get_string('competencyid', 'mathetrainer'));
        $mform->setType('competencyid', PARAM_TEXT);

        $mform->addElement('select', 'requirementlevel', get_string('requirementlevel', 'mathetrainer'), [
            'AFB I' => 'AFB I',
            'AFB II' => 'AFB II',
            'AFB III' => 'AFB III',
        ]);

        $mform->addElement('select', 'difficulty', get_string('difficulty', 'mathetrainer'), [1 => '1', 2 => '2', 3 => '3']);
        $mform->setDefault('difficulty', 1);

        $mform->addElement('text', 'itemcount', get_string('itemcount', 'mathetrainer'));
        $mform->setType('itemcount', PARAM_INT);
        $mform->setDefault('itemcount', 1);

        $this->standard_intro_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
