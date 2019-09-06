<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The main stupla configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_stupla
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_stupla_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $PAGE, $OUTPUT;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        if ($this->is_add()) {
            $elements = array(
                $mform->createElement('select', 'namesource', '',
                    array(
                        STUPLA_TEXTSOURCE_FILENAME => get_string("textsourcefilename", "stupla"),
                        STUPLA_TEXTSOURCE_FILEPATH => get_string("textsourcefilepath", "stupla"),
                        STUPLA_TEXTSOURCE_DATAJS   => get_string("textsourcedatajs", "stupla"),
                        STUPLA_TEXTSOURCE_SPECIFIC => get_string("textsourcespecific", "stupla")
                    )
                ),
                $mform->createElement('text', 'name', '', array('size' => '40'))
            );
            $mform->setType('namesource', PARAM_TEXT);
            $mform->addGroup($elements, 'name_elements', get_string('name'), array(' '), false);
            $mform->disabledIf('name_elements', 'namesource', 'ne', STUPLA_TEXTSOURCE_SPECIFIC);
            $mform->setDefault('namesource', STUPLA_TEXTSOURCE_DATAJS);
            $mform->addHelpButton('name_elements', 'nameadd', 'stupla');
        } else {
            $mform->addElement('text', 'name', get_string('name'), array('size' => '40'));
            if (!empty($CFG->formatstringstriptags)) {
                $mform->setType('name', PARAM_TEXT);
            } else {
                $mform->setType('name', PARAM_CLEAN);
            }
            $mform->addElement('hidden', 'namesource', STUPLA_TEXTSOURCE_SPECIFIC);
            $mform->setType('namesource', PARAM_TEXT);
            $mform->addRule('name', null, 'required', null, 'client');
            $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        }

        // Adding the standard "intro" and "introformat" fields.
        $this->add_intro_editor();

        // Subusers / sessions.
        $mform->addElement('header', 'sessionhandling', get_string('sessionhandling', 'stupla'));
        $mform->addElement('select', 'sessionsResumable', get_string('sessions', 'stupla'),
            array(
                STUPLA_SESS_ONLYONE => get_string('sessionsresumable_onlyone', 'stupla'),
                STUPLA_SESS_PRIMARYONE => get_string('sessionsresumable_primaryone', 'stupla'),
                STUPLA_SESS_PRIMARYMULTI => get_string('sessionsresumable_primarymulti', 'stupla'),
                STUPLA_SESS_ONLYMULTI => get_string('sessionsresumable_onlymulti', 'stupla')
            ));

        $mform->addElement('checkbox', 'useSubusers', get_string('useSubuser', 'stupla'));
        $mform->addHelpButton('useSubusers', 'useSubuser', 'stupla');
        $mform->addElement('html', '<div id="id_subuserPanel" class="fitem" style="display:none"><div class="fitemtitle">'.
            get_string('subuserLogData', 'stupla').' '.
            $OUTPUT->help_icon('subuserdata', 'stupla', get_string('subuserdata', 'stupla')).' '.
            $OUTPUT->help_icon('combodef', 'stupla', get_string('combodef', 'stupla')).'</div>'.
                '<div class="felement" id="subuserFields"></div>');
        $mform->addElement('text', 'subuserprotname', get_string('subuserprotname', 'stupla'));
        $mform->setType('subuserprotname', PARAM_TEXT);
        $mform->addHelpButton('subuserprotname', 'subuserprotname', 'stupla');
        $mform->addElement('html', '</div>');

        // Stupla embedding.
        $mform->addElement('header', 'referenceStupla', get_string('reference_stupla', 'stupla'));
        $mform->addElement('select', 'refInternExtern', get_string('reference_intern_extern', 'stupla'),
            array(
                0 => get_string('intern', 'stupla'),
                1 => get_string('extern', 'stupla')
            ));
        $mform->addHelpButton('refInternExtern', 'reference_intern_extern', 'stupla');
        $mform->addElement('html', '<div id="ref0">');
        $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1, 'mainfile' => true, 'accepted_types' => '*');
        $mform->addElement('filemanager', 'referenceInt', get_string('filename', 'stupla'), null, $options);
        $mform->addHelpButton('referenceInt', 'reference_int', 'stupla');
        $mform->addElement('html', '</div><div id="ref1">');
        $mform->addElement('text', 'reference', get_string('reference', 'stupla'), 'size="255"');
        $mform->setType('reference', PARAM_TEXT);
        $mform->addHelpButton('reference', 'reference_ext', 'stupla');
        $mform->addElement('text', 'localpath', get_string('localpath', 'stupla'), 'size="255"');
        $mform->setType('localpath', PARAM_TEXT);
        $mform->addHelpButton('localpath', 'localpath', 'stupla');
        $mform->addElement('html', '</div>');

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

        // JS.
        $PAGE->requires->js('/mod/stupla/edit.js');

        stupla_expand_subuserdata($this->current);

        $PAGE->requires->js_init_call('stupla_settings_init',
            array(
                array(
                    'subuserFieldname' => get_string('subuserFieldname', 'stupla'),
                    'subuserFieldmust' => get_string('subuserFieldmust', 'stupla'),
                    'subuserFieldtype' => get_string('subuserFieldtype', 'stupla'),
                    'subuserFieldcomment' => get_string('subuserFieldcomment', 'stupla'),
                    'delsufield' => get_string('delsufield', 'stupla'),
                    'textfield' => get_string('textfield', 'stupla'),
                    'datefield' => get_string('datefield', 'stupla'),
                    'combofield' => get_string('combofield', 'stupla'),
                    'su_combodata' => get_string('su_combodata', 'stupla'),
                    'subuser_new' => get_string('subuser_new', 'stupla'),
                    'addsufield' => get_string('addsufield', 'stupla'),
                    'subuserFieldname' => get_string('subuserFieldname', 'stupla'),
                    'subuserFieldname' => get_string('subuserFieldname', 'stupla'),
                ),
                $this->current->sudata
            ), true);
    }

    public function data_preprocessing(&$stupla) {

        $stupla['referenceInt'] = 0;
        if ($this->is_add()) {
            $contextid = null;
        } else {
            $contextid = $this->context->id;
        }
        $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1);
        file_prepare_draft_area($stupla['referenceInt'], $contextid, 'mod_stupla', 'sourcefile', 0, $options);

        if ( isset($stupla['flags']) ) {
            $stupla['sessionsResumable'] = ($stupla['flags'] & STUPLA_SESS_MASK);
            $stupla['useSubusers'] = (($stupla['flags'] & STUPLA_USESUBUSERS) != 0);
        }
        $stupla['refInternExtern'] = $this->is_update() && stupla_is_extern($stupla['reference']) ? 1 : 0;
    }

    /**
     * Detects if we are adding a new Stupla activity
     * as opposed to updating an existing one
     *
     * Note: we could use any of the following to detect add:
     *   - empty($this->_instance | _cm)
     *   - empty($this->current->add | id | coursemodule | instance)
     *
     * @return bool True if we are adding an new activity instance, false otherwise
     */
    public function is_add() {
        if (empty($this->current->instance)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Detects if we are updating a new StuPla activity
     * as opposed to adding an new one
     *
     * @return bool True if we are adding an new activity instance, false otherwise
     */
    public function is_update() {
        if (empty($this->current->instance)) {
            return false;
        } else {
            return true;
        }
    }

    public function get_subuserdata() {
        $res = array();
        $sm = $this->_form->_submitValues;
        for ($i = 0; isset($this->_form->_submitValues['su_type_'.$i]); $i++) {
            if ( !isset($mform->{"su_del_".$i}) ) {
                $res[] = array(
                    "name" => $sm['su_name_'.$i],
                    "type" => $sm['su_type_'.$i],
                    "must" => isset($sm['su_must_'.$i]),
                    "comment" => $sm['su_comment_'.$i],
                    "data" => $sm['su_data_'.$i]);
            }
        }
        return $res;
    }

}
