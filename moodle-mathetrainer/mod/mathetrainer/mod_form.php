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

/**
 * Settings form of the activity.
 */
class mod_mathetrainer_mod_form extends moodleform_mod {
    /**
     * Defines the form elements.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), ['size' => 64]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'curriculumhdr', get_string('curriculumsettings', 'mathetrainer'));
        // The core settings of this activity: always show them expanded.
        $mform->setExpanded('curriculumhdr');

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

        $schoolforms = ['' => get_string('any', 'mathetrainer')];
        foreach (['gymnasium', 'realschule', 'gesamtschule', 'sekundarschule', 'gemeinschaftsschule', 'hauptschule'] as $form) {
            $schoolforms[$form] = get_string('schoolform_' . $form, 'mathetrainer');
        }
        $mform->addElement('select', 'schoolform', get_string('schoolform', 'mathetrainer'), $schoolforms);
        $mform->addHelpButton('schoolform', 'schoolform', 'mathetrainer');

        $levels = [
            '' => get_string('any', 'mathetrainer'),
            'AFB I' => 'AFB I',
            'AFB II' => 'AFB II',
            'AFB III' => 'AFB III',
        ];
        $mform->addElement('select', 'requirementlevel', get_string('requirementlevel', 'mathetrainer'), $levels);

        $competencies = [];
        if (class_exists(\local_curriculum_math\mapper\competency_query_service::class)) {
            global $DB;
            $competencies = (new \local_curriculum_math\mapper\competency_query_service($DB))->competency_options();
        }
        if ($competencies) {
            // Curriculum plugin with imported packs: choose from the list, grouped by state.
            $groups = ['' => ['' => get_string('nocompetency', 'mathetrainer')]];
            foreach ($competencies as $state => $options) {
                $groups[$state] = $options;
            }
            $mform->addElement('selectgroups', 'competencyid', get_string('competencyid', 'mathetrainer'), $groups);
        } else {
            $mform->addElement('text', 'competencyid', get_string('competencyid', 'mathetrainer'), ['size' => 40]);
        }
        $mform->setType('competencyid', PARAM_ALPHANUMEXT);
        $mform->addHelpButton('competencyid', 'competencyid', 'mathetrainer');

        // Gradebook: maximum grade / scale and how several rounds are combined.
        $this->standard_grading_coursemodule_elements();
        $mform->setDefault('grade', 100);
        $methods = [];
        foreach (\mod_mathetrainer\local\grading::methods() as $method => $key) {
            $methods[$method] = get_string($key, 'mathetrainer');
        }
        $mform->addElement('select', 'grademethod', get_string('grademethod', 'mathetrainer'), $methods);
        $mform->addHelpButton('grademethod', 'grademethod', 'mathetrainer');
        $mform->setDefault('grademethod', \mod_mathetrainer\local\grading::METHOD_HIGHEST);
        $mform->hideIf('grademethod', 'grade[modgrade_type]', 'eq', 'none');

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
